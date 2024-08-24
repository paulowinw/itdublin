<?php
/**
 * AffiliateCreateAffiliate.
 * php version 5.6
 *
 * @category AffiliateCreateAffiliate
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;

/**
 * AffiliateCreateAffiliate
 *
 * @category AffiliateCreateAffiliate
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class AffiliateCreateAffiliate extends AutomateAction {

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
	public $action = 'affiliate_create_affiliate';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Create Affiliate', 'suretriggers' ),
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
		$affiliate                  = [];
		$affiliate['user_name']     = $selected_options['user_login'];
		$affiliate['status']        = $selected_options['status'];
		$affiliate['rate_type']     = $selected_options['rate_type'];
		$affiliate['referral_rate'] = $selected_options['referral_rate'];
		$affiliate['payment_email'] = $selected_options['payment_email'];
		$affiliate['notes']         = $selected_options['notes'];
		$affiliate['welcome_email'] = $selected_options['welcome_email'];
		$affiliate['welcome_email'] = ( 'true' === $affiliate['welcome_email'] ) ? true : false;

		$wp_user = get_user_by( 'login', $affiliate['user_name'] );

		if ( ! function_exists( 'affwp_is_affiliate' ) || ! function_exists( 'affwp_add_affiliate' ) || ! function_exists( 'affwp_get_affiliate' ) ) {
			throw new Exception( 'AffiliateWP functions not found.' );
		}

		if ( false === $wp_user ) {
			throw new Exception( 'User does not exist.' );
		}

		$is_affiliate = affwp_is_affiliate( $wp_user->data->ID );

		if ( false !== $is_affiliate ) {
			throw new Exception( 'User is already an affiliate.' );
		}

		$affiliate_id = affwp_add_affiliate( $affiliate );

		if ( false === $affiliate_id ) {
			throw new Exception( 'Not able to create new affiliate, try later.' );
		} else {
			$affiliate      = affwp_get_affiliate( $affiliate_id );
			$affiliate_data = get_object_vars( $affiliate );
			return $affiliate_data;
		}
	}
}

AffiliateCreateAffiliate::get_instance();
