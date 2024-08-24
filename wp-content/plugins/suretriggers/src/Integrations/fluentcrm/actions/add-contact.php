<?php
/**
 * AddContact.
 * php version 5.6
 *
 * @category AddContact
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\FluentCRM\Actions;

use DateTime;
use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;

/**
 * AddContact
 *
 * @category AddContact
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class AddContact extends AutomateAction {


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
	public $action = 'fluentcrm_add_contact';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {

		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Add/Update Contact', 'suretriggers' ),
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
	 *
	 * @return array|void
	 *
	 * @throws Exception Exception.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {

		if ( empty( $selected_options['contact_email'] ) || ! is_email( $selected_options['contact_email'] ) ) {
			throw new Exception( 'Email address is invalid.' );
		}
		$forced_update = false;

		$contact_api = FluentCrmApi( 'contacts' );
		$contact     = $contact_api->getContact( trim( $selected_options['contact_email'] ) );

		if ( ! is_null( $contact ) ) {
			$forced_update = true;
		} 
		

		$data = [
			'email' => trim( $selected_options['contact_email'] ),
		];

		$data['prefix']         = ( isset( $selected_options['prefix'] ) ) ? $selected_options['prefix'] : '';
		$data['first_name']     = ( isset( $selected_options['first_name'] ) ) ? $selected_options['first_name'] : '';
		$data['last_name']      = ( isset( $selected_options['last_name'] ) ) ? $selected_options['last_name'] : '';
		$data['address_line_1'] = ( isset( $selected_options['address_line_1'] ) ) ? $selected_options['address_line_1'] : '';
		$data['address_line_2'] = ( isset( $selected_options['address_line_2'] ) ) ? $selected_options['address_line_2'] : '';
		$data['city']           = ( isset( $selected_options['city'] ) ) ? $selected_options['city'] : '';
		$data['state']          = ( isset( $selected_options['state'] ) ) ? $selected_options['state'] : '';
		$data['postal_code']    = ( isset( $selected_options['postal_code'] ) ) ? $selected_options['postal_code'] : '';
		$data['country']        = ( isset( $selected_options['country'] ) ) ? $selected_options['country'] : '';
		$data['phone']          = ( isset( $selected_options['phone'] ) ) ? $selected_options['phone'] : '';
		$dob                    = ( isset( $selected_options['date_of_birth'] ) ) ? $selected_options['date_of_birth'] : '';
	   
		if ( '' !== $dob ) {
			$date_of_birth = DateTime::createFromFormat( 'Y-m-d', $dob );
			if ( ! $date_of_birth ) {
				throw new Exception( "The date format does not conform to the 'yyyy-mm-dd' format in Date of Birth field." );
			}
			$data['date_of_birth'] = $dob;
		}

		if ( ! empty( $selected_options['contact_status'] ) ) {
			$data['status'] = $selected_options['contact_status'];
		}

		if ( isset( $selected_options['show_custom_fields'] ) 
			&& in_array( $selected_options['show_custom_fields'], [ true, 1, 'true', '1' ], true ) ) {
			$fcrm_custom_fields = fluentcrm_get_custom_contact_fields();
			foreach ( $selected_options['field_row_repeater'] as $key => $field ) {
				$type       = $fcrm_custom_fields[ $key ]['type'];
				$label      = $fcrm_custom_fields[ $key ]['label'];
				$field_name = $field['value']['name'];
				$value      = trim( $selected_options['field_row'][ $key ][ $field_name ] );

				if ( empty( $value ) ) {
					continue;
				}

				if ( in_array( $type, [ 'select-one', 'radio' ], true ) ) {
					$field_options = $fcrm_custom_fields[ $key ]['options'];
					$field_value   = null;

					foreach ( $field_options as $option ) {
						if ( strtolower( $value ) === strtolower( $option ) ) {
							$field_value = $option;
						}
					}

					if ( ! $field_value ) {
						throw new Exception( "The value '" . $value . "' is not a valid option in the " . $label . ' field in FluentCRM.' );
					}

					$data[ $field_name ] = $field_value;

				} elseif ( in_array( $type, [ 'select-multi', 'checkbox' ], true ) ) {
					$option_values = explode( ',', $value );
					$option_values = array_map( 'trim', $option_values );
					$field_options = $fcrm_custom_fields[ $key ]['options'];

					$options = [];
					foreach ( $option_values as $option_value ) {
						$field_value = null;

						foreach ( $field_options as $option ) {
							if ( strtolower( $option_value ) === strtolower( $option ) ) {
								$field_value = $option;
							}
						}

						if ( ! $field_value ) {
							throw new Exception( "The value '" . $option_value . "' is not a valid option in the " . $label . ' field in FluentCRM.' );
						}

						$options[] = $field_value;
					}

					
					$data[ $field_name ] = $options;
					
				} elseif ( 'date' === $type ) {
					$date = DateTime::createFromFormat( 'Y-m-d', $value );
					if ( ! $date ) {
						throw new Exception( "The date format does not conform to the 'yyyy-mm-dd' format in " . $label . ' field.' );
					}

					$data[ $field_name ] = $value;
				} elseif ( 'date_time' === $type ) {
					$date = DateTime::createFromFormat( 'Y-m-d H:i:s', $value );
					if ( ! $date ) {
						throw new Exception( "The datetime format does not conform to the 'yyyy-mm-dd hh:mm:ss' format in " . $label . ' field.' );
					}

					$data[ $field_name ] = $value;
				} else {
					$data[ $field_name ] = $value;
				}
			}
		}

		$contact = $contact_api->createOrUpdate( $data, $forced_update );

		if ( 'pending' === $contact->status ) {
			$contact->sendDoubleOptinEmail();
		}

		$tag_ids   = [];
		$tag_names = [];
		if ( isset( $selected_options['tag_id'] ) && is_array( $selected_options['tag_id'] ) && ! empty( $selected_options['tag_id'] ) ) {
			foreach ( $selected_options['tag_id'] as $tag ) {
				$tag_ids[]   = $tag['value'];
				$tag_names[] = esc_html( $tag['label'] );
			}

			$contact->attachTags( $tag_ids );
		}

		$list_ids   = [];
		$list_names = [];
		if ( isset( $selected_options['list_id'] ) && is_array( $selected_options['list_id'] ) && ! empty( $selected_options['list_id'] ) ) {
			foreach ( $selected_options['list_id'] as $list ) {
				$list_ids[]   = $list['value'];
				$list_names[] = esc_html( $list['label'] );
			}

			$contact->attachLists( $list_ids );
		}

		if ( ! $contact ) {
			throw new Exception( 'Invalid contact.' );
		}

		$custom_data = $contact->custom_fields();

		$context                   = [];
		$context['full_name']      = $contact->full_name;
		$context['first_name']     = $contact->first_name;
		$context['last_name']      = $contact->last_name;
		$context['contact_owner']  = $contact->contact_owner;
		$context['company_id']     = $contact->company_id;
		$context['email']          = $contact->email;
		$context['address_line_1'] = $contact->address_line_1;
		$context['address_line_2'] = $contact->address_line_2;
		$context['postal_code']    = $contact->postal_code;
		$context['city']           = $contact->city;
		$context['state']          = $contact->state;
		$context['country']        = $contact->country;
		$context['phone']          = $contact->phone;
		$context['status']         = $contact->status;
		$context['contact_type']   = $contact->contact_type;
		$context['source']         = $contact->source;
		$context['date_of_birth']  = $contact->date_of_birth;
		$context['list_names']     = implode( ',', $list_names );
		$context['tag_names']      = implode( ',', $tag_names );

		if ( ! empty( $custom_data ) ) {
			foreach ( $custom_data as $key => $field ) {
				if ( is_array( $field ) ) {
					$context[ $key ] = implode( ',', $field );
				} else {
					$context[ $key ] = $field;
				}
			}
		}
		return $context;
	}

}

AddContact::get_instance();
