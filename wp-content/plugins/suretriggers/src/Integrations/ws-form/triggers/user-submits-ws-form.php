<?php
/**
 * UserSubmitsWSForm.
 * php version 5.6
 *
 * @category UserSubmitsWSForm
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\WSForm\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'UserSubmitsWSForm' ) ) :

	/**
	 * UserSubmitsWSForm
	 *
	 * @category UserSubmitsWSForm
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class UserSubmitsWSForm {

		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'WSForm';

		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'user_submits_wsform';

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
				'action'        => 'user_submits_wsform',
				'common_action' => 'wsf_submit_post_complete',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 1,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param object $form_submit submission_data.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $form_submit ) {
			if ( empty( $form_submit ) ) {
				return;
			}

			if ( property_exists( $form_submit, 'form_object' ) ) {
				if ( function_exists( 'wsf_form_get_fields' ) ) {
					$fields = wsf_form_get_fields( $form_submit->form_object );
					foreach ( $fields as $field ) {

						if ( ! isset( $field->type ) ) {
							continue;
						}
						$field_type = $field->type;

						if ( function_exists( 'wsf_config_get_field_types' ) ) {
							$field_types = wsf_config_get_field_types();
				
							if ( ! isset( $field_types[ $field_type ] ) ) {
								continue;
							}
							$field_type_config = $field_types[ $field_type ];
							$submit_save       = isset( $field_type_config['submit_save'] ) ? $field_type_config['submit_save'] : false;
			
							if ( ! $submit_save ) {
								continue;
							}
						}
				
						if ( property_exists( $field, 'id' ) ) {
							$field_name = $field->id;
							if ( function_exists( 'wsf_submit_get_value' ) ) {
								$field_value = wsf_submit_get_value( $form_submit, 'field_' . $field_name );
						
								if ( property_exists( $field, 'label' ) ) {
									$context[ $field->label ] = $field_value;
								}
							}
						}                   
					}
				}
			}

			if ( function_exists( 'wsf_config_get_field_types' ) ) {
				if ( property_exists( $form_submit, 'form_id' ) ) {
					$form_id            = absint( $form_submit->form_id );
					$context['form_id'] = $form_id;

					AutomationController::sure_trigger_handle_trigger(
						[
							'trigger' => $this->trigger,
							'context' => $context,
						]
					);
				}
			}
		}
	}

	/**
	 * Ignore false positive
	 *
	 * @psalm-suppress UndefinedMethod
	 */
	UserSubmitsWSForm::get_instance();

endif;
