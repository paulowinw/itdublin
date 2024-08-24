<?php
/**
 * UserSubmitsPaymentGravityForm.
 * php version 5.6
 *
 * @category UserSubmitsPaymentGravityForm
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\GravityForms\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;

if ( ! class_exists( 'UserSubmitsPaymentGravityForm' ) ) :

	/**
	 * UserSubmitsPaymentGravityForm
	 *
	 * @category UserSubmitsPaymentGravityForm
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class UserSubmitsPaymentGravityForm {


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
		public $trigger = 'user_submits_payment_gravityform';

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
				'label'         => __( 'User Submits Payment Form', 'suretriggers' ),
				'action'        => 'user_submits_payment_gravityform',
				'common_action' => 'gform_post_payment_completed',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 2,
			];

			return $triggers;

		}

		/**
		 * Trigger listener
		 *
		 * @param array $entry The Entry object.
		 * @param array $action The Action Object.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $entry, $action ) {

			$context['gravity_form']          = (int) $entry['form_id'];
			$context['entry_id']              = $entry['id'];
			$context['user_ip']               = $entry['ip'];
			$context['entry_source_url']      = $entry['source_url'];
			$context['entry_submission_date'] = $entry['date_created'];
			$context['payment_status']        = $entry['payment_status'];
			$context['payment_amount']        = $entry['payment_amount'];
			$context['currency']              = $entry['currency'];
			$context['payment_method']        = $entry['payment_method'];
			$context['transaction_id']        = $entry['transaction_id'];
			$context['user']                  = WordPress::get_user_context( $entry['created_by'] );
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
	UserSubmitsPaymentGravityForm::get_instance();

endif;
