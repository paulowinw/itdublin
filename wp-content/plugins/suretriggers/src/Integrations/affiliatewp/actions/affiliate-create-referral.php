<?php
/**
 * AffiliateCreateReferral.
 * php version 5.6
 *
 * @category AffiliateCreateReferral
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;

/**
 * AffiliateCreateReferral
 *
 * @category AffiliateCreateReferral
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class AffiliateCreateReferral extends AutomateAction {

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
	public $action = 'affiliate_create_referral';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Create Referral for specific Affiliate', 'suretriggers' ),
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
		$affiliate_id = $selected_options['affiliate_id'];

		if ( ! function_exists( 'affwp_get_affiliate_user_id' ) || ! function_exists( 'affwp_is_affiliate' ) 
		|| ! function_exists( 'affwp_add_referral' ) || ! function_exists( 'affwp_get_referral' ) ) {
			throw new Exception( 'AffiliateWP functions not found.' );
		}

		$affiliate_user_id = affwp_get_affiliate_user_id( $affiliate_id );

		if ( $affiliate_user_id && affwp_is_affiliate( $affiliate_user_id ) ) {
			$referral['amount']       = $selected_options['amount'];
			$referral['custom']       = $selected_options['custom'];
			$referral['status']       = $selected_options['status'];
			$referral['context']      = $selected_options['context'];
			$referral['reference']    = $selected_options['reference'];
			$referral['description']  = $selected_options['description'];
			$referral['type']         = $selected_options['type'];
			$referral['date']         = $selected_options['referral_date'];
			$referral['affiliate_id'] = $affiliate_id;
			$referral['user_id']      = $affiliate_user_id;
			$user                     = get_user_by( 'id', $affiliate_user_id );
			if ( $user ) {
				$referral['user_name'] = $user->user_login;
			}
			$referral_id = affwp_add_referral( $referral );
			
			if ( $referral_id ) {
				$referral      = affwp_get_referral( $referral_id );
				$referral_data = get_object_vars( $referral );
				return $referral_data;
			} else {
				throw new Exception( 'We are unable to add referral.' );
			}       
		} else {
			throw new Exception( 'The user is not an affiliate.' );
		}
	}
}

AffiliateCreateReferral::get_instance();
