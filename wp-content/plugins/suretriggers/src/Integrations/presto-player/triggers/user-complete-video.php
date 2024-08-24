<?php
/**
 * UserCompleteVideo.
 * php version 5.6
 *
 * @category UserCompleteVideo
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\PrestoPlayer\Triggers;

use PrestoPlayer\Models\Video;
use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'UserCompleteVideo' ) ) :

	/**
	 * UserCompleteVideo
	 *
	 * @category UserCompleteVideo
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class UserCompleteVideo {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'PrestoPlayer';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'user_video_completes';

		use SingletonLoader;


		/**
		 * Constructor
		 *
		 * @since  1.0.0
		 */
		public function __construct() {
			add_filter( 'sure_trigger_register_trigger', [ $this, 'register' ] );
		}

		/**
		 * Register action.
		 *
		 * @param array $triggers trigger data.
		 * @return array
		 */
		public function register( $triggers ) {

			$triggers[ $this->integration ][ $this->trigger ] = [
				'label'         => __( 'Video Completed', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'presto_player_progress',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 3,
			];

			return $triggers;

		}

		/**
		 * Trigger listener
		 *
		 * @param array $video_id The entry that was just created.
		 * @param int   $percent The current form.
		 * @param int   $visit_time Visit time.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $video_id, $percent, $visit_time ) {
			if ( 100 === $percent ) {

				$user_id                        = ap_get_current_user_id();
				$context                        = WordPress::get_user_context( $user_id );
				$context['pp_video']            = $video_id;
				$context['pp_video_percentage'] = $percent;
				$video_data                     = ( new Video( $video_id ) )->toArray();
				$context['video']               = $video_data;
				if ( is_array( $video_data ) && array_key_exists( 'post_id', $video_data ) ) {
					$media_tags = get_the_terms( $video_data['post_id'], 'pp_video_tag' );
					if ( ! empty( $media_tags ) && is_array( $media_tags ) && isset( $media_tags[0] ) ) {
						$tag_name = [];
						foreach ( $media_tags as $tag ) {
							$tag_name[] = $tag->name;
						}
						$context['media']['tag'] = $tag_name;
					}
					$mediahub_data = WordPress::get_post_context( $video_data['post_id'] );
					$context       = array_merge( $context, $mediahub_data );
				}

				AutomationController::sure_trigger_handle_trigger(
					[
						'trigger' => $this->trigger,
						'context' => $context,
					]
				);
			}
		}
	}

	/**
	 * Ignore false positive
	 *
	 * @psalm-suppress UndefinedMethod
	 */
	UserCompleteVideo::get_instance();

endif;
