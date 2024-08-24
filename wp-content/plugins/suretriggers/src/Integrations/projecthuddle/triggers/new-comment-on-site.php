<?php
/**
 * NewCommentOnSite.
 * php version 5.6
 *
 * @category NewCommentOnSite
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\ProjectHuddle\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'NewCommentOnSite' ) ) :

	/**
	 * NewCommentOnSite
	 *
	 * @category NewCommentOnSite
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class NewCommentOnSite {


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
		public $trigger = 'new_comment_on_site';

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
				'label'         => __( 'New Comment On Site', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'rest_insert_comment',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 3,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param object|array $comment  Inserted or updated comment object.
		 * @param array        $request  Request object.
		 * @param bool         $creating True when creating a comment, false
		 *                         when updating.
		 * @return void
		 */
		public function trigger_listener( $comment, $request, $creating ) {

			if ( ! $creating ) {
				return;
			}

			if ( is_object( $comment ) ) {
				$comment = get_object_vars( $comment );
			}
			
			$context['website_id'] = (int) $comment['project_id'];

			$context         = $comment;
			$comment_item_id = get_comment_meta( $comment['comment_ID'], 'item_id' );
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
	NewCommentOnSite::get_instance();

endif;
