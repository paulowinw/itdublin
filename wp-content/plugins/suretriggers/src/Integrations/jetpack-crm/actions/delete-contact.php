<?php
/**
 * DeleteContact.
 * php version 5.6
 *
 * @category DeleteContact
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\JetpackCRM\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;

/**
 * DeleteContact
 *
 * @category DeleteContact
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class DeleteContact extends AutomateAction {


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
	public $action = 'jetpack_crm_delete_contact';

	use SingletonLoader;

	/**
	 * Register an action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {

		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Delete Contact', 'suretriggers' ),
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

		if ( ! function_exists( 'zeroBS_deleteCustomer' ) ) {
			throw new Exception( 'Seems like Jetpack CRM plugin is not installed correctly.' );
		}

		$contact = sanitize_text_field( $selected_options['contact'] );

		global $wpdb;
		$contact_id = $wpdb->get_var( $wpdb->prepare( "SELECT `ID` FROM `{$wpdb->prefix}zbs_contacts` WHERE ID = %d OR zbsc_email LIKE %s", $contact, $contact ) );

		if ( empty( $contact_id ) ) {
			throw new Exception( 'Contact not found with this ID or Email.' );
		}

		zeroBS_deleteCustomer( $contact_id, false );

		$context               = [];
		$context['contact_id'] = $contact_id;

		return $context;
	}

}

DeleteContact::get_instance();
