<?php
/**
 * AddProductToCart.
 * php version 5.6
 *
 * @category AddProductToCart
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

if ( ! class_exists( 'AddProductToCart' ) ) :


	/**
	 * AddProductToCart
	 *
	 * @category AddProductToCart
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 */
	class AddProductToCart {

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
		public $trigger = 'woocommerce_add_to_cart';

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
				'label'         => __( 'Product is added to cart', 'suretriggers' ),
				'action'        => $this->trigger,
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 6,
			];

			return $triggers;
		}

		/**
		 *  Trigger listener
		 *
		 * @param int   $cart_item_key cart item key.
		 * @param int   $product_id product id.
		 * @param int   $quantity quantity.
		 * @param int   $variation_id variation id.
		 * @param int   $variation variation.
		 * @param array $cart_item_data cart item data.
		 *
		 * @return void
		 */
		public function trigger_listener( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
			
			$user_id               = ap_get_current_user_id();
			$context               = WordPress::get_user_context( $user_id );
			$context['product_id'] = $product_id;
			$context['product']    = WooCommerce::get_product_context( $product_id );
			$terms                 = get_the_terms( $product_id, 'product_cat' );
			if ( ! empty( $terms ) && is_array( $terms ) && isset( $terms[0] ) ) {
				$cat_name = [];
				foreach ( $terms as $cat ) {
					$cat_name[] = $cat->name;
				}
				$context['product']['category'] = implode( ', ', $cat_name );
			}
			$terms_tags = get_the_terms( $product_id, 'product_tag' );
			if ( ! empty( $terms_tags ) && is_array( $terms_tags ) && isset( $terms_tags[0] ) ) {
				$tag_name = [];
				foreach ( $terms_tags as $tag ) {
					$tag_name[] = $tag->name;
				}
				$context['product']['tag'] = implode( ', ', $tag_name );
			}
			unset( $context['product']['id'] );

			$context['product_quantity'] = $quantity;

			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger' => $this->trigger,
					'context' => $context,
				]
			);
		}
	}

	AddProductToCart::get_instance();

endif;
