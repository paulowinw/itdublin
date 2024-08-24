<?php
/**
 * Paymattic.
 * php version 5.6
 *
 * @category PaymentFormSubmitted
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\Paymattic\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'PaymentFormSubmitted' ) ) :

	/**
	 * PaymentFormSubmitted
	 *
	 * @category PaymentFormSubmitted
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class PaymentFormSubmitted {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'Paymattic';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'wppay_payment_form_submitted';

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
				'label'         => __( 'Payment Form Completed', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'wppayform/after_form_submission_complete',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 20,
				'accepted_args' => 2,
			];

			return $triggers;

		}

		/**
		 * Trigger listener
		 *
		 * @param array   $submission submission.
		 * @param integer $form_id form Id.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $submission, $form_id ) {
			if ( ! isset( $form_id ) ) {
				return;
			}
			$context = $submission;
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
	PaymentFormSubmitted::get_instance();

endif;
