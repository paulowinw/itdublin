<?php
/**
 * PurchaseProduct.
 * php version 5.6
 *
 * @category PurchaseProduct
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
use WC_Product;

if ( ! class_exists( 'PurchaseProduct' ) ) :

	/**
	 * PurchaseProduct
	 *
	 * @category PurchaseProduct
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class PurchaseProduct {

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
		public $trigger = 'wc_purchase_product';

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
				'label'         => __( 'User purchases a product', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'woocommerce_order_status_changed',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 4,
			];

			return $triggers;
		}

		/**
		 *  Trigger listener
		 *
		 * @param int    $order_id order ID.
		 * @param string $from_status order old status.
		 * @param string $to_status  order new status.
		 * @param array  $order  order.
		 * 
		 * @return void
		 */
		public function trigger_listener( $order_id, $from_status, $to_status, $order ) {
			if ( ! $order_id ) {
				return;
			}
			$order = wc_get_order( $order_id );
			
			if ( ! $order ) {
				return;
			}

			$user_id = $order->get_customer_id();

			$items       = $order->get_items();
			$product_ids = [];
			$product     = [];
			foreach ( $items as $item ) {   
				$product       = wc_get_product( $item['product_id'] );           
				$product_ids[] = $item['product_id'];
			}

			$is_virtual      = $product->is_virtual();
			$is_downloadable = $product->is_downloadable();
			
  
			if ( ( ! $is_virtual || ! $is_downloadable ) && 'processing' !== $to_status ) {
				return;
			}  
			
			$product_data = [];
			foreach ( $product_ids as $key => $product_id ) {
				/**
				 *
				 * Ignore line
				 *
				 * @phpstan-ignore-next-line
				 */
				$product_data[ 'product' . $key ] = WooCommerce::get_product_context( $product_id );
				/**
				 *
				 * Ignore line
				 *
				 * @phpstan-ignore-next-line
				 */
				$terms = get_the_terms( $product_id, 'product_cat' );
				if ( ! empty( $terms ) && is_array( $terms ) && isset( $terms[0] ) ) {
					$cat_name = [];
					foreach ( $terms as $cat ) {
						$cat_name[] = $cat->name;
					}
					$product_data[ 'product' . $key ]['category'] = implode( ', ', $cat_name );
				}
				/**
				 *
				 * Ignore line
				 *
				 * @phpstan-ignore-next-line
				 */
				$terms_tags = get_the_terms( $product_id, 'product_tag' );
				if ( ! empty( $terms_tags ) && is_array( $terms_tags ) && isset( $terms_tags[0] ) ) {
					$tag_name = [];
					foreach ( $terms_tags as $tag ) {
						$tag_name[] = $tag->name;
					}
					$product_data[ 'product' . $key ]['tag'] = implode( ', ', $tag_name );
				}
				$product = wc_get_product( $product_id );
				/**
				 *
				 * Ignore line
				 *
				 * @phpstan-ignore-next-line
				 */
				if ( $product->is_downloadable() ) {
					/**
					 *
					 * Ignore line
					 *
					 * @phpstan-ignore-next-line
					 */
					foreach ( $product->get_downloads() as $key_download_id => $download ) {
						$download_name                                = $download->get_name();
						$download_link                                = $download->get_file();
						$download_id                                  = $download->get_id();
						$download_type                                = $download->get_file_type();
						$download_ext                                 = $download->get_file_extension();
						$product_data[ 'product' . $key ]['download'] = [
							'download_name' => $download_name,
							'download_link' => $download_link,
							'download_id'   => $download_id,
							'download_type' => $download_type,
							'download_ext'  => $download_ext,
						];
					}
				}                       
			}

			$context = array_merge(
				WooCommerce::get_order_context( $order_id ),
				$product_data,
				WordPress::get_user_context( $user_id )
			);

			$context['total_items_in_order'] = count( $product_ids );
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
	PurchaseProduct::get_instance();

endif;
