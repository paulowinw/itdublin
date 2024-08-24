<?php
/**
 * AffiliateLinkCustomer.
 * php version 5.6
 *
 * @category AffiliateLinkCustomer
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;

/**
 * AffiliateLinkCustomer
 *
 * @category AffiliateLinkCustomer
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class AffiliateLinkCustomer extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'AffiliateWP';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'affiliate_link_customer';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Link Customer to an Affiliate for Lifetime Commissions', 'suretriggers' ),
			'action'   => $this->action,
			'function' => [ $this, 'action_listener' ],
		];
		return $actions;
	}

	/**
	 * Action listener.
	 *
	 * @param int   $user_id user_id.
	 * @param int   $automation_id automation_id.
	 * @param array $fields fields.
	 * @param array $selected_options selectedOptions.
	 * @psalm-suppress UndefinedMethod
	 * @throws Exception Exception.
	 * 
	 * @return array|bool|void
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		
		$lifetime_user_name = $selected_options['affiliate_id'];
		$affiliate_user     = get_user_by( 'ID', $lifetime_user_name );
		$user_email         = $selected_options['customer_email'];

		if ( ! function_exists( 'affiliate_wp' ) || ! function_exists( 'affwp_get_customer' ) || ! function_exists( 'affwp_add_customer' ) || ! function_exists( 'affiliate_wp_lifetime_commissions' ) ) {
			throw new Exception( 'AffiliateWP functions not found.' );
		}

		if ( is_email( $user_email ) ) {
			$user = get_user_by( 'email', $user_email );
			if ( $user ) {
				$user_ID = $user->ID;
				if ( $affiliate_user ) {
					// Prevent affiliates from setting themselves as a lifetime customer.
					if ( $affiliate_user->ID === $user_ID ) {
						throw new Exception( 'Can not add affiliate themselves as a lifetime customer' );
					}

					$customer = affiliate_wp()->customers->get_by( 'user_id', $user_ID );

					// If the customer does not exist, create it.
					if ( ! $customer ) {
						$customer = affwp_get_customer(
							affwp_add_customer(
								[
									'user_id' => $user_ID,
									'email'   => $user->user_email,
								] 
							) 
						);
					}

					$lifetime_customer = affiliate_wp_lifetime_commissions()
					->lifetime_customers->get_by( 'affwp_customer_id', $customer->customer_id );
					$affiliate         = '';

					$affiliate = affiliate_wp()->affiliates->get_by( 'affiliate_id', $affiliate_user->ID );

					// If the affiliate was unset, and this user is currently a customer, delink the affiliate and bail.
					if ( $lifetime_customer && ! $affiliate ) {
						affiliate_wp_lifetime_commissions()
						->lifetime_customers->delete( $lifetime_customer->lifetime_customer_id );
						throw new Exception( 'Affiliate was not set.' );
					}

					// Add a new lifetime customer if the provided lifetime customer does not exist.
					if ( ! $lifetime_customer ) {
						affiliate_wp_lifetime_commissions()->lifetime_customers->add(
							[
								'affwp_customer_id' => $customer->customer_id,
								'affiliate_id'      => $affiliate->affiliate_id,
							] 
						);
						return affiliate_wp_lifetime_commissions()
						->lifetime_customers->get_by( 'affwp_customer_id', $customer->customer_id );
						// Otherwise, confirm the affiliate is actually a new affiliate before changing.
					} elseif ( $affiliate->affiliate_id !== $lifetime_customer->affiliate_id ) {
						affiliate_wp_lifetime_commissions()->lifetime_customers->update( 
							$lifetime_customer->affwp_customer_id,
							[
								'affiliate_id' => $affiliate->affiliate_id,
							] 
						);
						return affiliate_wp_lifetime_commissions()
						->lifetime_customers->get_by( 'affwp_customer_id', $customer->customer_id );
					}
				} else {
					throw new Exception( 'Affiliate User not exists.' );
				}
			} else {
				throw new Exception( 'User not exists.' );
			}
		} else {
			throw new Exception( 'Please enter valid email address.' );
		}
	}
}

AffiliateLinkCustomer::get_instance();
