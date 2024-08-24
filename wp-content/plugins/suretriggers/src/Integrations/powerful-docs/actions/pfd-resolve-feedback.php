<?php
/**
 * PfdResolveFeedback.
 * php version 5.6
 *
 * @category PfdResolveFeedback
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;

/**
 * PfdResolveFeedback
 *
 * @category PfdResolveFeedback
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class PfdResolveFeedback extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'PowerfulDocs';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'pfd_resolve_feedback';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Resolve Feedback', 'suretriggers' ),
			'action'   => $this->action,
			'function' => [ $this, 'action_listener' ],
		];
		return $actions;
	}

	/**
	 * Action listener.
	 *
	 * @param int   $user_id user_id.
	 * @param int   $automation_id automation_id.
	 * @param array $fields fields.
	 * @param array $selected_options selectedOptions.
	 * @psalm-suppress UndefinedMethod
	 * @throws Exception Exception.
	 * 
	 * @return array|bool|void
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		$search_word = $selected_options['search_term'];
		global $wpdb;

		$get_feedback = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}pfd_feedbacks WHERE comment LIKE %s", '%' . $wpdb->esc_like( $search_word ) . '%' ) );

		if ( ! empty( $get_feedback ) ) {
			try {
				$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}pfd_feedbacks WHERE comment = %s", $wpdb->esc_like( $search_word ) ) );
				return [
					'status'   => esc_attr__( 'Success', 'suretriggers' ),
					'response' => esc_attr__( 'Feedback resolved successfully', 'suretriggers' ),
				];
			} catch ( Exception $e ) {
				throw new Exception( 'Error! ' . $wpdb->last_error );
			}
		} else {
			throw new Exception( 'No feedbacks found related to search word.' );
		}
	}
}

PfdResolveFeedback::get_instance();
