<?php
/**
 * MemberPress core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\MemberPress;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\MemberPress
 */
class MemberPress extends Integrations {


	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'MemberPress';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'MemberPress', 'suretriggers' );
		$this->description = __( 'MemberPress will Help Build Membership Site.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/memberpress.svg';

		parent::__construct();
	}

	/**
	 * Fetch membership context.
	 *
	 * @param object $subscription subscription object.
	 * @return array
	 */
	public static function get_membership_context( $subscription ) {
		$context                                  = [];
		$context['membership_id']                 = $subscription->product_id;
		$context['membership_title']              = get_the_title( $subscription->product_id );
		$context['amount']                        = $subscription->amount;
		$context['total']                         = $subscription->total;
		$context['tax_amount']                    = $subscription->tax_amount;
		$context['tax_rate']                      = $subscription->tax_rate;
		$context['trans_num']                     = $subscription->trans_num;
		$context['status']                        = $subscription->status;
		$context['subscription_id']               = $subscription->subscription_id;
		$context['membership_url']                = get_permalink( $subscription->product_id );
		$context['membership_featured_image_id']  = get_post_meta( $subscription->product_id, '_thumbnail_id', true );
		$context['membership_featured_image_url'] = get_the_post_thumbnail_url( $subscription->product_id );

		return $context;
	}

	/**
	 * Fetch membership context.
	 *
	 * @param object $subscription subscription object.
	 * @return array
	 */
	public static function get_subscription_context( $subscription ) {
		$context                     = [];
		$context['membership_id']    = $subscription->product_id;
		$context['membership_title'] = get_the_title( $subscription->product_id );
		$context['user_id']          = $subscription->user_id;
		$context['amount']           = $subscription->price;
		$context['total']            = $subscription->total;
		$context['tax_amount']       = $subscription->tax_amount;
		$context['tax_rate']         = $subscription->tax_rate;
		$context['status']           = $subscription->status;
		$context['subscription_id']  = $subscription->id;

		$context['membership_url']                = get_permalink( $subscription->product_id );
		$context['membership_featured_image_id']  = get_post_meta( $subscription->product_id, '_thumbnail_id', true );
		$context['membership_featured_image_url'] = get_the_post_thumbnail_url( $subscription->product_id );

		return $context;
	}

	/**
	 * Is Plugin depended on plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( 'MeprCtrlFactory' );
	}

}

IntegrationsController::register( MemberPress::class );
