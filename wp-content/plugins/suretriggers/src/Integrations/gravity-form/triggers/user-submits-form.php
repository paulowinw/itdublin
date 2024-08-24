<?php
/**
 * UserSubmitsGravityForm.
 * php version 5.6
 *
 * @category UserSubmitsGravityForm
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\GravityForms\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'UserSubmitsGravityForm' ) ) :

	/**
	 * UserSubmitsGravityForm
	 *
	 * @category UserSubmitsGravityForm
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class UserSubmitsGravityForm {


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
		public $trigger = 'user_submits_gravityform';

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
				'label'         => __( 'Form Submitted', 'suretriggers' ),
				'action'        => 'user_submits_gravityform',
				'common_action' => 'gform_after_submission',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 2,
			];

			return $triggers;

		}

		/**
		 * Trigger listener
		 *
		 * @param array $entry The entry that was just created.
		 * @param array $form The current form.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $entry, $form ) {

			if ( empty( $entry ) ) {
				return;
			}
			$user_id = ap_get_current_user_id();

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
							$context[ $label_key ] = rgar( $entry, (string) $input['id'] );
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
					$context[ $label_key ] = rgar( $entry, (string) $field->id );
				}
			}

			$context['gravity_form']          = (int) $form['id'];
			$context['form_title']            = $form['title'];
			$context['entry_id']              = $entry['id'];
			$context['user_ip']               = $entry['ip'];
			$context['entry_source_url']      = $entry['source_url'];
			$context['entry_submission_date'] = $entry['date_created'];

			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger' => $this->trigger,
					'user_id' => $user_id,
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
	UserSubmitsGravityForm::get_instance();

endif;
