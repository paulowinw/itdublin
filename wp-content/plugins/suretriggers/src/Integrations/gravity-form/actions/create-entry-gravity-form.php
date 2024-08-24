<?php
/**
 * CreateEntryGravityForm.
 * php version 5.6
 *
 * @category CreateEntryGravityForm
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\GravityForms\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use GFAPI;

/**
 * CreateEntryGravityForm
 *
 * @category CreateEntryGravityForm
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class CreateEntryGravityForm extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'GravityForms';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'create_entry_gravity_form';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 *
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Create Entry for Form', 'suretriggers' ),
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
	 * @return array|void
	 * @throws Exception Exception.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		$form_id = $selected_options['gravity_form'];
		
		$from_input_values            = [];
		$from_input_values['form_id'] = absint( $form_id );

		if ( ! class_exists( 'GFAPI' ) ) {
			return;
		}

		foreach ( $selected_options['field_row_repeater'] as $key => $field ) {
			$field_id                               = $field['value']['intern_name'];
			$field_name                             = $field['value']['name'];
			$value                                  = trim( $selected_options['field_row'][ $key ][ $field_name ] );
			$from_input_values[ trim( $field_id ) ] = $value;
		}

		$input_values = [];
		foreach ( $from_input_values as $key => $value ) {
			if ( 'form_id' !== $key ) {
				if ( str_contains( $key, '.' ) ) {
					$str                             = str_replace( '.', '_', $key );
					$input_values[ 'input_' . $str ] = $value;
				} else {
					$input_values[ 'input_' . $key ] = $value;
				}
			}
		}
		
		$result = GFAPI::submit_form( $form_id, $input_values );
		if ( is_wp_error( $result ) ) {
			throw new Exception( $result->get_error_message() );
		}
		 
		if ( ! rgar( $result, 'is_valid' ) ) {
			$field_errors = rgar( $result, 'validation_messages' );
			throw new Exception( implode( ',', $field_errors ) );
		}

		if ( $result['is_valid'] ) {
			$entry_id     = $result['entry_id'];
			$entry_result = GFAPI::get_entry( $entry_id );
			foreach ( $selected_options['field_row_repeater'] as $key => $field ) {
				$entry_result[ $field['value']['title'] ] = rgar( $entry_result, (string) $field['value']['intern_name'] );
			}
			return $entry_result;
		}
	}
}

CreateEntryGravityForm::get_instance();
