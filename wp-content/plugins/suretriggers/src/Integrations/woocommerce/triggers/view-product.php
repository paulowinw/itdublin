<?php
/**
 * ViewProduct.
 * php version 5.6
 *
 * @category ViewProduct
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\Woocommerce\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\WooCommerce\WooCommerce;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

/**
 * ViewProduct
 *
 * @category ViewProduct
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class ViewProduct {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'WooCommerce';

	/**
	 * Trigger name.
	 *
	 * @var string
	 */
	public $trigger = 'wp_view_product';

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
			'label'         => __( 'User views a product', 'suretriggers' ),
			'action'        => $this->trigger,
			'common_action' => 'template_redirect',
			'function'      => [ $this, 'trigger_listener' ],
			'priority'      => 10,
			'accepted_args' => 1,
		];

		return $triggers;
	}

	/**
	 * Trigger listener
	 *
	 * @return void
	 */
	public function trigger_listener() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		if ( ! is_product() ) {
			return;
		}

		$user_id                    = ap_get_current_user_id();
		$product_id                 = get_queried_object_id();
		$product_data['product_id'] = $product_id;
		$product_data['product']    = WooCommerce::get_product_context( $product_id );
		$terms                      = get_the_terms( $product_id, 'product_cat' );
		if ( ! empty( $terms ) && is_array( $terms ) && isset( $terms[0] ) ) {
			$cat_name = [];
			foreach ( $terms as $cat ) {
				$cat_name[] = $cat->name;
			}
			$product_data['product']['category'] = implode( ', ', $cat_name );
		}
		$terms_tags = get_the_terms( $product_id, 'product_tag' );
		if ( ! empty( $terms_tags ) && is_array( $terms_tags ) && isset( $terms_tags[0] ) ) {
			$tag_name = [];
			foreach ( $terms_tags as $tag ) {
				$tag_name[] = $tag->name;
			}
			$product_data['product']['tag'] = implode( ', ', $tag_name );
		}
		unset( $product_data['product']['id'] ); //phpcs:ignore

		$context = array_merge(
			$product_data,
			WordPress::get_user_context( $user_id )
		);
		AutomationController::sure_trigger_handle_trigger(
			[
				'trigger' => $this->trigger,
				'context' => $context,
			]
		);
	}
}

ViewProduct::get_instance();
