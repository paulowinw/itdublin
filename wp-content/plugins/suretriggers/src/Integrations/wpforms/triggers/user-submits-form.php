<?php
/**
 * UserSubmitsForm.
 * php version 5.6
 *
 * @category UserSubmitsForm
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\Wpforms\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'UserSubmitsForm' ) ) :

	/**
	 * UserSubmitsForm
	 *
	 * @category UserSubmitsForm
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class UserSubmitsForm {

		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'WPForms';

		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'user_submits_wpform';

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
				'action'        => 'user_submits_wpform',
				'common_action' => 'wpforms_process_complete',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 4,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param array $fields Sanitized entry field values/properties.
		 * @param array $entry Original $_POST global.
		 * @param array $form_data Processed form settings/data, prepared to be used later.
		 * @param int   $entry_id Entry ID.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $fields, $entry, $form_data, $entry_id ) {
			if ( empty( $form_data ) ) {
				return;
			}

			$user_id                    = ap_get_current_user_id();
			$context                    = [];
			$context['form_id']         = (int) $form_data['id'];
			$context['form_title']      = isset( $form_data['settings'] ) ? $form_data['settings']['form_title'] : '';
			$context['submission_date'] = gmdate( 'd M Y', strtotime( $form_data['created'] ) );

			foreach ( $fields as $field ) {
				if ( 'name' === $field['type'] ) {
					if ( ! empty( $field['first'] ) || ! empty( $field['middle'] ) || ! empty( $field['last'] ) ) {
						if ( ! empty( $field['first'] ) ) {
							$context['First Name'] = $field['first'];
						}
						if ( ! empty( $field['middle'] ) ) {
							$context['Middle Name'] = $field['middle'];
						}
						if ( ! empty( $field['last'] ) ) {
							$context['Last Name'] = $field['last'];
						}
					} else {
						$context[ $field['name'] ] = $field['value'];
					}
				} else {
					$context[ $field['name'] ] = $field['value'];
				}
			}

			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger'    => $this->trigger,
					'wp_user_id' => $user_id,
					'context'    => $context,
				]
			);
		}
	}

	/**
	 * Ignore false positive
	 *
	 * @psalm-suppress UndefinedMethod
	 */
	UserSubmitsForm::get_instance();

endif;
