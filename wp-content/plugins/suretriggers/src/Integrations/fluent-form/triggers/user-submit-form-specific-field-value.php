<?php
/**
 * UserSubmitsSpecificFieldValueFluentForm.
 * php version 5.6
 *
 * @category UserSubmitsSpecificFieldValueFluentForm
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\FluentForm\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Models\Utilities;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'UserSubmitsSpecificFieldValueFluentForm' ) ) :

	/**
	 * UserSubmitsSpecificFieldValueFluentForm
	 *
	 * @category UserSubmitsSpecificFieldValueFluentForm
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class UserSubmitsSpecificFieldValueFluentForm {

		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'FluentForm';

		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'user_submits_specific_field_value_fluentform';

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
				'label'         => __( 'User Submits Form with Specific Field Value', 'suretriggers' ),
				'action'        => 'user_submits_specific_field_value_fluentform',
				'common_action' => 'fluentform/before_insert_submission',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 3,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param array  $insert_data submission_data Array.
		 * @param array  $data $_POST[‘data’] from submission.
		 * @param object $form The $form Object.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $insert_data, $data, $form ) {
			if ( empty( $form ) ) {
				return;
			}

			$context = (array) json_decode( $insert_data['response'], true );

			if ( isset( $form->id ) ) {
				$context['form_id'] = (int) $form->id;
			}
			if ( isset( $form->title ) ) {
				$context['form_title'] = $form->title;
			}
			$context['entry_id']         = $insert_data['serial_number'];
			$context['entry_source_url'] = $insert_data['source_url'];
			$context['submission_date']  = $insert_data['created_at'];
			$context['user_ip']          = $insert_data['ip'];
			foreach ( $data as $key => $value ) {
				if ( is_array( $value ) ) {
					foreach ( $value as $keyy => $valuye ) {
						$context['field_id']    = $keyy;
						$context['field_value'] = $valuye;
						AutomationController::sure_trigger_handle_trigger(
							[
								'trigger' => $this->trigger,
								'context' => $context,
							]
						);
					}
				} else {
					$context['field_id']    = $key;
					$context['field_value'] = $value;
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
	UserSubmitsSpecificFieldValueFluentForm::get_instance();

endif;
