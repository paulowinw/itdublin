<?php
/**
 * CommentResolved.
 * php version 5.6
 *
 * @category CommentResolved
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\ProjectHuddle\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'CommentResolved' ) ) :

	/**
	 * CommentResolved
	 *
	 * @category CommentResolved
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class CommentResolved {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'ProjectHuddle';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'ph_comment_resolved';

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
				'label'         => __( 'Comment Marked As Resolved', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'suretriggers_ph_after_comment_approval',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 1,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param object $comment Inserted or updated comment object.
		 * @return void
		 */
		public function trigger_listener( $comment ) {
			global $wpdb;

			if ( is_object( $comment ) ) {
				$comment = get_object_vars( $comment );
			}

			$id = $comment['ID'];

			$comment_result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT  wp_comments.comment_ID
					FROM wp_comments 
					WHERE comment_post_ID = %d
					LIMIT 0,1',
					$id
				),
				ARRAY_A
			);

			$comment_id            = $comment_result['comment_ID'];
			$meta_value            = get_comment_meta( $comment_id, 'project_id', true );
			$context['website_id'] = $meta_value;
			
			$context         = $comment;
			$comment_item_id = get_comment_meta( $comment_id, 'item_id' );
			if ( ! empty( $comment_item_id ) && is_array( $comment_item_id ) ) {
				$context['comment_item_id']         = $comment_item_id[0];
				$context['comment_item_page_title'] = get_the_title( (int) $comment_item_id[0] );
				$context['comment_item_page_url']   = get_post_meta( (int) $comment_item_id[0], 'page_url', true );
			}
			
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
	CommentResolved::get_instance();

endif;
