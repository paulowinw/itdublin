<?php
/**
 * CommentReplyCreatedTimeline.
 * php version 5.6
 *
 * @category CommentReplyCreatedTimeline
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\Voxel\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;

if ( ! class_exists( 'CommentReplyCreatedTimeline' ) ) :

	/**
	 * CommentReplyCreatedTimeline
	 *
	 * @category CommentReplyCreatedTimeline
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class CommentReplyCreatedTimeline {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'Voxel';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'voxel_comment_reply_created_timeline';

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
				'label'         => __( 'New Comment Reply Created on Timeline', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'voxel/app-events/timeline/comment-reply:created',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 1,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param object $event Event.
		 * @return void
		 */
		public function trigger_listener( $event ) {
			if ( ! property_exists( $event, 'reply' ) || ! property_exists( $event, 'comment' ) ) {
				return;
			}
			$context['replied_by'] = WordPress::get_user_context( $event->reply->get_user_id() );
			$context['comment_by'] = WordPress::get_user_context( $event->comment->get_user_id() );
			$context['comment']    = $event->comment->get_content();
			$context['comment_id'] = $event->comment->get_id();
			$context['reply_id']   = $event->reply->get_id();
			$context['reply']      = $event->reply->get_content();
	
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
	CommentReplyCreatedTimeline::get_instance();

endif;
