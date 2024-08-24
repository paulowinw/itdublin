<?php
/**
 * UserRepliesTopicForum.
 * php version 5.6
 *
 * @category UserRepliesTopicForum
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\wpForo\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;

if ( ! class_exists( 'UserRepliesTopicForum' ) ) :

	/**
	 * UserRepliesTopicForum
	 *
	 * @category UserRepliesTopicForum
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class UserRepliesTopicForum {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'wpForo';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'user_replies_topic_forum';

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
				'label'         => __( 'User Replies to Topic in Forum', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'wpforo_after_add_post',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 5,
				'accepted_args' => 2,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param array $post Post.
		 * @param array $topic Topic.
		 * @return void
		 */
		public function trigger_listener( $post, $topic ) {

			if ( ! isset( $topic['topicid'] ) || ! isset( $topic['forumid'] ) ) {
				return;
			}

			if ( ! absint( $post['postid'] ) ) {
				return;
			}

			if ( ! function_exists( 'WPF' ) ) {
				return;
			}
	
			$forum_id = absint( $topic['forumid'] );
			$topic_id = absint( $topic['topicid'] );

			$context['forum_id'] = $forum_id;
			$context['topic_id'] = $topic_id;
			$context['forum']    = WPF()->forum->get_forum( $forum_id );
			$context['topic']    = WPF()->topic->get_topic( $topic_id );
			$context['reply']    = WPF()->post->get_post( $post['postid'] );

			$context['user'] = WordPress::get_user_context( $post['userid'] );
	
			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger' => $this->trigger,
					'context' => $context,
				]
			);
		}
	}

	/**
	 * Ignore false positive
	 *
	 * @psalm-suppress UndefinedMethod
	 */
	UserRepliesTopicForum::get_instance();

endif;
