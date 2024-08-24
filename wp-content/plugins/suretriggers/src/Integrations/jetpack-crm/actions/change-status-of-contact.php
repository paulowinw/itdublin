<?php
/**
 * ChangeContactStatus.
 * php version 5.6
 *
 * @category ChangeContactStatus
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\JetpackCRM\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Integrations\JetpackCRM\JetpackCRM;
use SureTriggers\Traits\SingletonLoader;

/**
 * ChangeContactStatus
 *
 * @category ChangeContactStatus
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class ChangeContactStatus extends AutomateAction {


	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'JetpackCRM';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'jetpack_crm_change_contact_status';

	use SingletonLoader;

	/**
	 * Register an action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {

		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Change Contact Status', 'suretriggers' ),
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
	 * @param array $selected_options selected_options.
	 *
	 * @return array
	 *
	 * @throws Exception Exception.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		$status        = $selected_options['status'];
		$contact_email = $selected_options['contact_email'];
	
		// Check if status or contact email is empty, if so, exit the function.
		if ( empty( $status ) || empty( $contact_email ) ) {
			throw new Exception( __( 'Status or contact email is empty.', 'suretriggers' ) );
		}
	
		global $wpdb;
	
		// Prepare and execute the query to fetch contact by email..
		$contact = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT `ID`, `zbsc_status` FROM `{$wpdb->prefix}zbs_contacts` WHERE zbsc_email = %s", 
				$contact_email
			)
		);
	
		// If contact is found and the status does not match, update the status.
		if ( ! empty( $contact ) && $status !== $contact->zbsc_status ) {
			$wpdb->update(
				"{$wpdb->prefix}zbs_contacts",
				[ 'zbsc_status' => $status ],
				[ 'ID' => $contact->ID ]
			);
			return [
				'contact_status' => $status,
				'contact_email'  => $contact_email,
				'success'        => true,
				'msg'            => __( 'The contact status has been updated.', 'suretriggers' ),
			];
		} else {
			throw new Exception( __( 'Contact was not found matching.', 'suretriggers' ) );
		}
	}
}

ChangeContactStatus::get_instance();
