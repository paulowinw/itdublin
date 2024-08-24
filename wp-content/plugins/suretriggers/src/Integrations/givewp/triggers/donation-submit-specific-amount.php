<?php
/**
 * GiveWPDonationSubmitSpecificAmount.
 * php version 5.6
 *
 * @category GiveWPDonationSubmitSpecificAmount
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\GiveWP\Triggers;

use Give_Payment;
use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'GiveWPDonationSubmitSpecificAmount' ) ) :

	/**
	 * GiveWPDonationSubmitSpecificAmount
	 *
	 * @category GiveWPDonationSubmitSpecificAmount
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class GiveWPDonationSubmitSpecificAmount {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'GiveWP';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'givewp_donation_submit_specific_amount';

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
				'label'         => __( 'User Submits Donation Form Specific Amount', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'give_insert_payment',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 2,
			];

			return $triggers;

		}

		/**
		 * Trigger listener
		 *
		 * @param int   $payment_id ID of payment.
		 * @param array $payment_data Payment Data.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $payment_id, $payment_data ) {
			if ( ! class_exists( 'Give_Payment' ) ) {
				return;
			}
			$payment = new Give_Payment( $payment_id );
			if ( ! function_exists( 'give_get_donor_donation_comment' ) ) {
				return;
			}
			$address_data                 = $payment->address;
			$context['first_name']        = $payment->first_name;
			$context['last_name']         = $payment->last_name;
			$context['email']             = $payment->email;
			$context['currency']          = $payment->currency;
			$context['donated_amount']    = $payment->subtotal;
			$context['donation_amount']   = $payment->subtotal;
			$context['form_id']           = (int) $payment->form_id;
			$context['form_title']        = $payment->form_title;
			$context['name_title_prefix'] = $payment->title_prefix;
			$context['date']              = $payment->date;
			if ( is_array( $address_data ) ) {
				$context['address_line_1'] = $address_data['line1'];
				$context['address_line_2'] = $address_data['line2'];
				$context['city']           = $address_data['city'];
				$context['state']          = $address_data['state'];
				$context['zip']            = $address_data['zip'];
				$context['country']        = $address_data['country'];
			}
			// Payment meta.
			$payment_meta = $payment->get_meta();
			if ( is_array( $payment_meta ) && isset( $payment_meta['user_info'] ) ) {
				unset( $payment_meta['user_info'] );
			}
			$context['payment_meta'] = $payment_meta;
			$donor_comment           = give_get_donor_donation_comment( $payment_id, $payment->donor_id );
			$context['comment']      = $donor_comment->comment_content;
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
	GiveWPDonationSubmitSpecificAmount::get_instance();

endif;
