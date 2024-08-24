<?php
/**
 * AffiliateCreateReferralUser.
 * php version 5.6
 *
 * @category AffiliateCreateReferralUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;

/**
 * AffiliateCreateReferralUser
 *
 * @category AffiliateCreateReferralUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class AffiliateCreateReferralUser extends AutomateAction {

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
	public $action = 'affiliate_create_referral_user';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Create Referral for User', 'suretriggers' ),
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
		$user_email = $selected_options['wp_user_email'];

		if ( is_email( $user_email ) ) {
			$user = get_user_by( 'email', $user_email );
			if ( $user ) {
				if ( ! function_exists( 'affwp_add_referral' ) || ! function_exists( 'affwp_get_referral' ) || ! function_exists( 'affwp_get_affiliate_id' ) ) {
					throw new Exception( 'AffiliateWP function not found.' );
				}
				$affiliate_id = affwp_get_affiliate_id( $user->ID );
				if ( $affiliate_id ) {
					$referral['amount']       = $selected_options['amount'];
					$referral['custom']       = $selected_options['custom'];
					$referral['status']       = $selected_options['status'];
					$referral['context']      = $selected_options['context'];
					$referral['reference']    = $selected_options['reference'];
					$referral['description']  = $selected_options['description'];
					$referral['type']         = $selected_options['type'];
					$referral['date']         = $selected_options['referral_date'];
					$referral['affiliate_id'] = $affiliate_id;
					$referral['user_id']      = $user->ID;
					$referral['user_name']    = $user->user_login;

					$referral_id = affwp_add_referral( $referral );
					if ( $referral_id ) {
						$referral      = affwp_get_referral( $referral_id );
						$referral_data = get_object_vars( $referral );
						return $referral_data;
					} else {
						throw new Exception( 'We are unable to add referral.' );
					}
				} else {
					throw new Exception( 'User is not an affiliate.' );
				}
			} else {
				throw new Exception( 'User not exists.' );
			}
		} else {
			throw new Exception( 'Please enter valid email address.' );
		}
	}
}

AffiliateCreateReferralUser::get_instance();
