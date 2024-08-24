<?php
/**
 * UserSubmitsUAGForm.
 * php version 5.6
 *
 * @category UserSubmitsUAGForm
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\Spectra\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'UserSubmitsUAGForm' ) ) :

	/**
	 * UserSubmitsUAGForm
	 *
	 * @category UserSubmitsUAGForm
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class UserSubmitsUAGForm {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'Spectra';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'user_submits_spectraform';

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
				'label'         => __( 'User Submits Form', 'suretriggers' ),
				'action'        => 'user_submits_spectraform',
				'common_action' => 'uagb_form_success',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 1,
			];

			return $triggers;

		}

		/**
		 * Trigger listener
		 *
		 * @param array $form_data Form submitted data.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $form_data ) {
			
			if ( empty( $form_data ) ) {
				return;
			}
			$user_id                   = ap_get_current_user_id();
			$form_data['spectra_form'] = $form_data['id'];
			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger' => $this->trigger,
					'user_id' => $user_id,
					'context' => $form_data,
				]
			);
		}
	}

	/**
	 * Ignore false positive
	 *
	 * @psalm-suppress UndefinedMethod
	 */
	UserSubmitsUAGForm::get_instance();

endif;
