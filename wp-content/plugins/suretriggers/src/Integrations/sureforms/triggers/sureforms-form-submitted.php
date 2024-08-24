<?php
/**
 * SureFormsFormSubmitted.
 * php version 5.6
 *
 * @category SureFormsFormSubmitted
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\SureForms\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'SureFormsFormSubmitted' ) ) :

	/**
	 * SureFormsFormSubmitted
	 *
	 * @category SureFormsFormSubmitted
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class SureFormsFormSubmitted {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'SureForms';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'sureforms_form_submitted';

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
				'action'        => $this->trigger,
				'common_action' => 'srfm_form_submit',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 1,
			];
			return $triggers;

		}

		/**
		 * Trigger listener
		 *
		 * @param array $response Response Data.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $response ) {

			if ( empty( $response ) ) {
				return;
			}

			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger'    => $this->trigger,
					'wp_user_id' => ap_get_current_user_id(),
					'context'    => $response,
				]
			);
		}
	}

	/**
	 * Ignore false positive
	 *
	 * @psalm-suppress UndefinedMethod
	 */
	SureFormsFormSubmitted::get_instance();

endif;
