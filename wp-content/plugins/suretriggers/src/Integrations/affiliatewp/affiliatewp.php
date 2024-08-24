<?php
/**
 * AffiliateWP core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\AffiliateWP;

use Affiliate_WP;
use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\AffiliateWP
 */
class AffiliateWP extends Integrations {

	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'AffiliateWP';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'AffiliateWP', 'suretriggers' );
		$this->description = __( 'Affiliate Plugin for WordPress.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/affiliatewp.svg';

		parent::__construct();
	}

	/**
	 * Get affiliate context data.
	 *
	 * @param mixed  $affiliate affiliate.
	 * @param int    $user_id ID.
	 * @param string $status Affiliate status.
	 *
	 * @return array
	 */
	public static function get_affiliate_context( $affiliate, $user_id, $status ) {
		$user = get_userdata( $user_id );

		$context['affiliate_id']       = maybe_serialize( $affiliate->affiliate_id );
		$context['affiliate_url']      = maybe_serialize( affwp_get_affiliate_referral_url( [ 'affiliate_id' => $affiliate->affiliate_id ] ) );
		$context['affiliate_status']   = maybe_serialize( $status );
		$context['registration_date']  = maybe_serialize( $affiliate->date_registered );
		$context['website']            = maybe_serialize( $user->user_url );
		$context['referral_rate_type'] = ! empty( $affiliate->rate_type ) ? maybe_serialize( $affiliate->rate_type ) : maybe_serialize( '0' );
		$context['referral_rate']      = ! empty( $affiliate->rate ) ? maybe_serialize( $affiliate->rate ) : maybe_serialize( '0' );
		$dynamic_coupons               = affwp_get_dynamic_affiliate_coupons( $affiliate->affiliate_id, false );
		$coupons                       = '';

		if ( isset( $dynamic_coupons ) && is_array( $dynamic_coupons ) ) {
			foreach ( $dynamic_coupons as $coupon ) {
				$coupons .= $coupon->coupon_code . '<br/>';
			}
		}

		$context['dynamic_coupon']    = maybe_serialize( $coupons );
		$context['account_email']     = maybe_serialize( $user->user_email );
		$context['payment_email']     = maybe_serialize( $affiliate->payment_email );
		$context['promotion_methods'] = maybe_serialize( get_user_meta( $affiliate->user_id, 'affwp_promotion_method', true ) );
		$context['affiliate_notes']   = maybe_serialize( affwp_get_affiliate_meta( $affiliate->affiliate_id, 'notes', true ) );

		return $context;
	}

	/**
	 * Get affiliate referral context data.
	 *
	 * @param mixed $referral affiliate.
	 *
	 * @return array
	 */
	public static function get_referral_context( $referral ) {
		$context['referral_type']        = maybe_serialize( $referral->type );
		$context['referral_amount']      = maybe_serialize( $referral->amount );
		$context['referral_date']        = maybe_serialize( $referral->date );
		$context['referral_description'] = maybe_serialize( $referral->description );
		$context['referral_reference']   = maybe_serialize( $referral->reference );
		$context['referral_context']     = maybe_serialize( $referral->context );
		$context['referral_custom']      = maybe_serialize( $referral->custom );
		$context['referral_status']      = maybe_serialize( $referral->status );

		return $context;
	}

	/**
	 * Is Plugin dependent plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( Affiliate_WP::class );
	}

}

IntegrationsController::register( AffiliateWP::class );
