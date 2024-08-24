<?php
/**
 * FormEntryUpdatedGravityForm.
 * php version 5.6
 *
 * @category FormEntryUpdatedGravityForm
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\GravityForms\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;
use GFAPI;

if ( ! class_exists( 'FormEntryUpdatedGravityForm' ) ) :

	/**
	 * FormEntryUpdatedGravityForm
	 *
	 * @category FormEntryUpdatedGravityForm
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class FormEntryUpdatedGravityForm {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'GravityForms';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'form_entry_updated_gravityform';

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
				'label'         => __( 'Form Entry Updated', 'suretriggers' ),
				'action'        => 'form_entry_updated_gravityform',
				'common_action' => [ 'gform_after_update_entry', 'gform_post_update_entry' ],
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 3,
			];

			return $triggers;

		}

		/**
		 * Trigger listener
		 *
		 * @param array   $form           The form object for the entry.
		 * @param integer $entry_id     The entry ID.
		 * @param array   $original_entry The entry object before being updated.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $form, $entry_id, $original_entry ) {

			if ( ! class_exists( 'GFAPI' ) ) {
				return;
			}
			if ( empty( $entry_id ) ) {
				return;
			}
			
			$context['gravity_form']   = (int) $form['id'];
			$context['entry_id']       = $entry_id;
			$context['original_entry'] = $original_entry;
			$entry                     = GFAPI::get_entry( $entry_id );
			foreach ( $form['fields'] as $field ) {
				$inputs = $field->get_entry_inputs();
				if ( is_array( $inputs ) ) {
					foreach ( $inputs as $input ) {
						$label_key = strtolower( str_replace( ' ', '_', $input['label'] ) );
						if ( ! isset( $input['isHidden'] ) || ( isset( $input['isHidden'] ) && ! $input['isHidden'] ) ) {
							if ( ( 'fileupload' == $field['type'] && 1 == $field['multipleFiles'] ) || 'multiselect' == $field['type'] ) {
								$json_string = rgar( $entry, (string) $input['id'] );
								$array       = json_decode( $json_string );
								if ( is_array( $array ) ) {
									$comma_separated                       = implode( ',', $array );
									$context[ 'form_field_' . $label_key ] = $comma_separated;
								}
							}
							$context[ 'updated ' . $label_key ] = rgar( $entry, (string) $input['id'] );
						}
					}
				} else {
					$label_key = strtolower( str_replace( ' ', '_', $field['label'] ) );
					if ( ( 'fileupload' == $field['type'] && 1 == $field['multipleFiles'] ) || 'multiselect' == $field['type'] ) {
						$json_string = rgar( $entry, (string) $field->id );
						$array       = json_decode( $json_string );
						if ( is_array( $array ) ) {
							$comma_separated                       = implode( ',', $array );
							$context[ 'form_field_' . $label_key ] = $comma_separated;
						}
					}
					$context[ 'updated ' . $label_key ] = rgar( $entry, (string) $field->id );
				}
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
	FormEntryUpdatedGravityForm::get_instance();

endif;
