<?php
/**
 * UserCreatesNewTopicForum.
 * php version 5.6
 *
 * @category UserCreatesNewTopicForum
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

if ( ! class_exists( 'UserCreatesNewTopicForum' ) ) :

	/**
	 * UserCreatesNewTopicForum
	 *
	 * @category UserCreatesNewTopicForum
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class UserCreatesNewTopicForum {


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
		public $trigger = 'user_creates_new_topic_forum';

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
				'label'         => __( 'User Creates New Topic in Forum', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'wpforo_after_add_topic',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 5,
				'accepted_args' => 1,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param array $args Args.
		 * @return void
		 */
		public function trigger_listener( $args ) {

			if ( isset( $args['forumid'] ) ) {
				$forum_id = absint( $args['forumid'] );
			} else {
				return;
			}
	
			if ( isset( $args['topicid'] ) ) {
				$topic_id = absint( $args['topicid'] );
			} else {
				return;
			}

			if ( ! function_exists( 'WPF' ) ) {
				return;
			}

			$context['forum_id'] = $forum_id;
			$context['topic_id'] = $topic_id;
			$context['forum']    = WPF()->forum->get_forum( $forum_id );
			$context['topic']    = WPF()->topic->get_topic( $topic_id );
			$context['user']     = WordPress::get_user_context( $args['userid'] );
	
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
	UserCreatesNewTopicForum::get_instance();

endif;
