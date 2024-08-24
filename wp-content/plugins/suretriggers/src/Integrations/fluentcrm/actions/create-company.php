<?php
/**
 * CreateCompany.
 * php version 5.6
 *
 * @category CreateCompany
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\FluentCRM\Actions;

use Exception;
use DateTime;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use FluentCrm\App\Services\Helper;

/**
 * CreateCompany
 *
 * @category CreateCompany
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class CreateCompany extends AutomateAction {


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
	public $action = 'fluentcrm_create_company';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {

		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Create Company', 'suretriggers' ),
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

		if ( ! class_exists( 'FluentCrm\App\Services\Helper' ) || ! function_exists( 'FluentCrmApi' ) ) {
			return;
		}

		// Check if company module is enabled.
		$is_company_enabled = Helper::isCompanyEnabled();
		if ( ! $is_company_enabled ) {
			throw new Exception( 'Company module disabled. You can add companies and assign contacts to companies only when it is enabled!!' );
		}

		if ( '' != $selected_options['company_email'] && ! is_email( $selected_options['company_email'] ) ) {
			throw new Exception( 'Email address is invalid.' );
		}

		$data = [
			'email' => trim( $selected_options['company_email'] ),
		];

		$data['name']             = $selected_options['company_name'];
		$data['description']      = $selected_options['company_description'];
		$data['address_line_1']   = $selected_options['address_line_1'];
		$data['address_line_2']   = $selected_options['address_line_2'];
		$data['city']             = $selected_options['city'];
		$data['state']            = $selected_options['state'];
		$data['postal_code']      = $selected_options['postal_code'];
		$data['country']          = $selected_options['country'];
		$data['phone']            = $selected_options['phone'];
		$data['type']             = $selected_options['company_type'];
		$data['owner_id']         = $selected_options['company_owner_id'];
		$data['employees_number'] = $selected_options['company_employee_count'];
		$data['industry']         = $selected_options['company_industry'];
		$data['website']          = $selected_options['company_website'];
		$data['linkedin_url']     = $selected_options['company_linkedin_url'];
		$data['facebook_url']     = $selected_options['company_facebook_url'];
		$data['twitter_url']      = $selected_options['company_twitter_url'];

		if ( isset( $selected_options['show_custom_fields'] ) 
			&& in_array( $selected_options['show_custom_fields'], [ true, 1, 'true', '1' ], true ) && function_exists( 'fluentcrm_get_custom_company_fields' ) ) {
			$fcrm_custom_fields = fluentcrm_get_custom_company_fields();
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

					$data['custom_values'][ $field_name ] = $field_value;

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

					
					$data['custom_values'][ $field_name ] = $options;
					
				} elseif ( 'date' === $type ) {
					$date = DateTime::createFromFormat( 'Y-m-d', $value );
					if ( ! $date ) {
						throw new Exception( "The date format does not conform to the 'yyyy-mm-dd' format in " . $label . ' field.' );
					}

					$data['custom_values'][ $field_name ] = $value;
				} elseif ( 'date_time' === $type ) {
					$date = DateTime::createFromFormat( 'Y-m-d H:i:s', $value );
					if ( ! $date ) {
						throw new Exception( "The datetime format does not conform to the 'yyyy-mm-dd hh:mm:ss' format in " . $label . ' field.' );
					}

					$data['custom_values'][ $field_name ] = $value;
				} else {
					$data['custom_values'][ $field_name ] = $value;
				}
			}
		}

		$company = FluentCrmApi( 'companies' )->createOrUpdate( $data );

		return $company;
	}

}

CreateCompany::get_instance();
