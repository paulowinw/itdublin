<?php

namespace PrestoPlayer\Integrations\LearnDash;

use PrestoPlayer\Models\Post;
use PrestoPlayer\Support\Utility;
use PrestoPlayer\Contracts\Service;

class LearnDash implements Service {

	public function register() {
		add_action(
			'plugins_loaded',
			function () {
				if ( ! self::isEnabled() ) {
					return;
				}

				add_filter( 'learndash_settings_fields', array( $this, 'settingsFields' ), 10, 2 );
				add_filter( 'ld_video_provider', array( $this, 'addProvider' ), 10, 2 );
				add_filter( 'presto-settings-block-js-options', array( $this, 'jsOptions' ) );
				add_filter( 'get_post_metadata', array( $this, 'filterVideoURL' ), 10, 3 );

				add_filter( 'presto_player/block/default_attributes', array( $this, 'addVideoAttributes' ) );
				add_filter( 'presto_player/templates/player_tag', array( $this, 'addPlayerTags' ) );
			}
		);
	}

	/**
	 * Add tags to player
	 *
	 * @param array $data
	 * @return void
	 */
	public function addPlayerTags( $data ) {
		if ( empty( $data['cookieKey'] ) || empty( $data['videoProgress'] ) ) {
			return;
		}

		ob_start();
		?>
		data-video-cookie-key="<?php echo $data['cookieKey']; ?>"
		data-video-progression="<?php echo $data['videoProgress']; ?>"
		data-video-provider="presto"
		<?php
		echo ob_get_clean();
	}

	/**
	 * Add attributes to video for video progression
	 *
	 * @param array $attributes
	 * @return array
	 */
	public function addVideoAttributes( $attributes ) {
		global $post;

		// bail if not a learndash post type
		if ( ! self::isLearnDashPost( $post ) ) {
			return $attributes;
		}

		$logic_video     = true;
		$video_id        = ! empty( $attributes['id'] ) ? '(presto-' . $attributes['id'] . ')' : '(presto)';
		$video_completed = $this->stepIsCompleted( $post->ID );

		if ( $video_completed ) {
			$logic_video = false;
		} else {
			// get lesson settings
			$step_settings = learndash_get_setting( $post );

			if ( 'BEFORE' === ( $step_settings['lesson_video_shown'] ?? '' ) ) {
				$logic_video = true;

				$topics = learndash_get_topic_list( $post->ID );
				if ( ! empty( $topics ) ) {
					$progress = learndash_get_course_progress( null, $topics[0]->ID );
					if ( ! empty( $progress ) ) {
						$topics_completed = 0;
						foreach ( $progress['posts'] as $topic ) {
							if ( (int) 1 === (int) $topic->completed ) {
								++$topics_completed;
								break;
							}
						}

						if ( ! empty( $topics_completed ) ) {
							$logic_video = false;
						}
					}
				}
			}
		}

		if ( true === $logic_video ) {
			$logic_video_str = 'true';
		} else {
			$logic_video_str = 'false';
		}

		$attributes['cookieKey']     = $this->buildVideoCookieKey( $video_id );
		$attributes['videoProgress'] = $logic_video_str;

		return $attributes;
	}

	/**
	 * Build unique video progress cookie key. This is used to track the video state
	 * in the user's browser.
	 *
	 * @param integer $attach_id attachment ID of the video
	 *
	 * @return string $cookie_key.
	 */
	public function buildVideoCookieKey( $attach_id = '' ) {
		$cookie_key = '';
		$cookie_key = $this->getNonceSlug();

		if ( ( isset( $attach_id ) ) && ( ! empty( $attach_id ) ) ) {
			$lesson_video_url = trim( $attach_id );
			$lesson_video_url = html_entity_decode( $lesson_video_url );

			$cookie_key .= '_' . $lesson_video_url;
		}
		$cookie_key = 'learndash-video-progress-' . md5( $cookie_key );

		return $cookie_key;
	}

	/**
	 * Utility function to get the nonce slug.
	 *
	 * @since 3.2.3
	 */
	protected function getNonceSlug() {
		$post_id   = get_the_ID();
		$step_id   = $post_id;
		$course_id = learndash_get_course_id( $post_id );
		$user_id   = (int) get_current_user_id();

		return 'learndash_video_' . $user_id . '_' . $course_id . '_' . $step_id;
	}

	/**
	 * Dynamically replace (presto) with (presto-$video_id)
	 *
	 * @param mixed   $value
	 * @param integer $object_id
	 * @param string  $meta_key
	 *
	 * @return mixed
	 */
	public function filterVideoURL( $value, $object_id, $meta_key ) {
		// prevent recursion
		remove_filter( current_filter(), __FUNCTION__ );

		// only learndash meta
		if ( ! in_array( $meta_key, array( '_sfwd-topic', '_sfwd-lessons' ) ) ) {
			return $value;
		}

		// get meta
		$meta_cache = wp_cache_get( $object_id, 'post_meta' );
		if ( ! $meta_cache ) {
			$meta_cache = update_meta_cache( 'post', array( $object_id ) );
			if ( isset( $meta_cache[ $object_id ] ) ) {
				$meta_cache = $meta_cache[ $object_id ];
			} else {
				$meta_cache = null;
			}
		}
		if ( ! $meta_key ) {
			return $meta_cache;
		}

		if ( isset( $meta_cache[ $meta_key ] ) ) {
			$saved = maybe_unserialize( $meta_cache[ $meta_key ][0] );

			if ( is_array( $saved ) ) {
				$key = array_key_exists( 'sfwd-lessons_lesson_video_url', $saved ) ? 'sfwd-lessons_lesson_video_url' : '';
				$key = array_key_exists( 'sfwd-topic_lesson_video_url', $saved ) ? 'sfwd-topic_lesson_video_url' : $key;
			}

			if ( ! $key ) {
				return $value;
			}

			if ( strpos( $saved[ $key ], '(presto' ) === false ) {
				return $value;
			}

			$post_model                 = new Post( get_post( $object_id ) );
			$video_id                   = $post_model->findVideoId();
			$saved[ $key ]              = "(presto-$video_id)";
			$meta_cache[ $meta_key ][0] = $saved;
			return $meta_cache[ $meta_key ];
		}

		return $value;
	}

	/**
	 * Pass javascript options to presto player
	 *
	 * @param array $options
	 * @return void
	 */
	public function jsOptions( $options ) {
		if ( self::isEnabled() ) {
			global $post;
			$settings = learndash_get_setting( $post );

			if ( ! empty( $settings['lesson_video_auto_complete_delay'] ) ) {
				$post_type_obj                                  = get_post_type_object( $post->post_type );
				$settings['videos_auto_complete_delay_message'] = sprintf(
					// translators: placeholders: 1. Lesson or Topic label, 2. span for counter.
					wp_kses_post( _x( '<p class="ld-video-delay-message">%1$s will auto complete in %2$s seconds</p>', 'placeholders: 1. Lesson or Topic label, 2. span for counter', 'learndash' ) ),
					$post_type_obj->labels->singular_name,
					'<span class="time-countdown">' . $settings['lesson_video_auto_complete_delay'] . '</span>'
				);
			}

			$options['learndash'] = $settings;
		}

		return $options;
	}

	/**
	 * Is LearnDash enabled?
	 *
	 * @return boolean
	 */
	public static function isEnabled() {
		return defined( 'LEARNDASH_VERSION' );
	}

	/**
	 * Should the video load on the learndash page
	 *
	 * @return boolean
	 */
	public static function shouldVideoLoad() {
		global $post;

		// bail if not a learndash post type
		if ( ! self::isLearnDashPost( $post ) ) {
			return true;
		}

		// step is completed, load video
		if ( self::stepIsCompleted( $post ) ) {
			return true;
		}

		// check if lesson steps are complete
		return self::areStepsComplete( $post );
	}

	/**
	 * Is this a learndash post?
	 *
	 * @param \WP_Post $post
	 * @return boolean
	 */
	public static function isLearnDashPost( $post ) {
		if ( ! $post ) {
			return false;
		}

		return in_array( $post->post_type, array( 'sfwd-lessons', 'sfwd-topic' ) );
	}

	/**
	 * Is the learndash step completed
	 *
	 * @param \WP_Post $post
	 * @return bool
	 */
	public static function stepIsCompleted( $post ) {
		if ( ! function_exists( 'learndash_get_course_progress' ) ) {
			return true;
		}

		global $post;
		$progress = learndash_get_course_progress( null, $post->ID );
		return ( ! empty( $progress['this'] ) ) && ( $progress['this'] instanceof \WP_Post ) && ( true === (bool) $progress['this']->completed );
	}

	/**
	 * Are lesson/topic steps complete?
	 *
	 * @param \WP_Post $post
	 * @return boolean
	 */
	public static function areStepsComplete( \WP_Post $post ) {
		// get lesson settings
		$lesson_settings = learndash_get_setting( $post );

		// we're only concerned with "AFTER"
		if ( 'AFTER' !== ( $lesson_settings['lesson_video_shown'] ?? '' ) ) {
			return true;
		}

		// if this is a lesson, check if topics are completed
		if ( $post->post_type === 'sfwd-lessons' ) {
			if ( ! learndash_lesson_topics_completed( $post->ID ) ) {
				return false;
			}
		}

		// quizes must also be completed
		return self::areQuizzesCompleted( $post );
	}

	/**
	 * Are quizzes completed?
	 *
	 * @param \WP_Post $post
	 * @return boolean
	 */
	public static function areQuizzesCompleted( \WP_Post $post ) {
		// quizes must also be completed
		$quizzes_completed   = true;
		$lesson_quizzes_list = learndash_get_lesson_quiz_list( $post->ID );
		if ( ! empty( $lesson_quizzes_list ) ) {
			foreach ( $lesson_quizzes_list as $quiz ) {
				if ( 'completed' !== $quiz['status'] ) {
					$quizzes_completed = false;
					break;
				}
			}
		}
		return (bool) $quizzes_completed;
	}

	/**
	 * Add our video provider to learndash
	 *
	 * @param array $video_data
	 * @param array $step_settings
	 * @return array
	 */
	public function addProvider( $video_data, $step_settings ) {
		if ( strpos( $step_settings['lesson_video_url'], '(presto' ) !== false ) {
			return 'presto';
		}
		return $video_data;
	}

	/**
	 * Adds our setting to the lesson settings page
	 *
	 * @param array  $settings
	 * @param string $meta_box_key
	 * @return array
	 */
	public function settingsFields( $settings, $meta_box_key ) {
		// if it's not one of these settings pages, bail
		if ( ! in_array( $meta_box_key, array( 'learndash-lesson-display-content-settings', 'learndash-topic-display-content-settings' ) ) ) {
			return $settings;
		}

		$setting = array(
			'lesson_use_presto_video' => array(
				'name'           => 'lesson_use_presto_video',
				'label'          => esc_html__( 'Use Presto Video', 'learndash' ),
				'type'           => 'checkbox-switch',
				'value'          => ! empty( $settings['use_presto_video'] ) ? $settings['use_presto_video'] : '',
				'help_text'      => esc_html__( 'Use the Presto Player video in your post content for video progression.', 'learndash' ),
				'default'        => '',
				'options'        => array(
					'on' => esc_html__( 'The presto video in this post will be used for video progression.', 'learndash' ),
					''   => '',
				),
				'parent_setting' => 'lesson_video_enabled',
			),
		);

		// insert before video url
		$settings = Utility::arrayInsert( $settings, $setting, 'lesson_video_url', 'before' );

		return $settings;
	}
}
