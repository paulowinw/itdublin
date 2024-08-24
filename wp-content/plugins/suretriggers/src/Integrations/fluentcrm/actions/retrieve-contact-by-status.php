<?php
/**
 * RetrieveContactByStatus.
 * php version 5.6
 *
 * @category RetrieveContactByStatus
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\FluentCRM\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;

/**
 * RetrieveContactByStatus
 *
 * @category RetrieveContactByStatus
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class RetrieveContactByStatus extends AutomateAction {


	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'FluentCRM';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'fluentcrm_retrieve_contact_by_status';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {

		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Retrieve Contact By Status', 'suretriggers' ),
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
	 * @return array
	 * @throws Exception Exception.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		if ( ! function_exists( 'FluentCrmApi' ) ) {
			throw new Exception( 'FluentCRM is not active.' );
		}

		$status_arr  = $selected_options['contact_status'];
		$contact_api = FluentCrmApi( 'contacts' );
		
		$arr = [];
		foreach ( $status_arr as $status ) {
			$arr[] = $status['value'];
		}

		$contact = $contact_api->getInstance()
				->with( [ 'tags', 'lists' ] )
				->whereIn( 'status', $arr )
				->get();

		$contacts = json_decode( $contact, true );

		if ( empty( $contacts ) ) {
			throw new Exception( 'No Contacts Found.' );
		}

		$context = [];
		if ( is_array( $contacts ) ) {
			foreach ( $contacts as $key => $value ) {
				$context['contact'][ $key ]['id']             = $value['id'];
				$context['contact'][ $key ]['user_id']        = $value['user_id'];
				$context['contact'][ $key ]['full_name']      = $value['full_name'];
				$context['contact'][ $key ]['first_name']     = $value['first_name'];
				$context['contact'][ $key ]['last_name']      = $value['last_name'];
				$context['contact'][ $key ]['contact_owner']  = $value['contact_owner'];
				$context['contact'][ $key ]['company_id']     = $value['company_id'];
				$context['contact'][ $key ]['email']          = $value['email'];
				$context['contact'][ $key ]['address_line_1'] = $value['address_line_1'];
				$context['contact'][ $key ]['address_line_2'] = $value['address_line_2'];
				$context['contact'][ $key ]['postal_code']    = $value['postal_code'];
				$context['contact'][ $key ]['city']           = $value['city'];
				$context['contact'][ $key ]['state']          = $value['state'];
				$context['contact'][ $key ]['country']        = $value['country'];
				$context['contact'][ $key ]['phone']          = $value['phone'];
				$context['contact'][ $key ]['status']         = $value['status'];
				$context['contact'][ $key ]['contact_type']   = $value['contact_type'];
				$context['contact'][ $key ]['source']         = $value['source'];
				$context['contact'][ $key ]['date_of_birth']  = $value['date_of_birth'];
				$context['contact'][ $key ]['tags']           = $value['tags'];
				$context['contact'][ $key ]['lists']          = $value['lists'];
				$custom_data                                  = $value['custom_fields'];
				if ( ! empty( $custom_data ) ) {
					foreach ( $custom_data as $custom_key => $field ) {
						if ( is_array( $field ) ) {
							$context['contact'][ $key ][ $custom_key ] = implode( ',', $field );
						} else {
							$context['contact'][ $key ][ $custom_key ] = $field;
						}
					}
				}
			}
		}
		return $context;
	}

}

RetrieveContactByStatus::get_instance();
