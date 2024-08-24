<?php
/**
 * PurchaseProductCategory.
 * php version 5.6
 *
 * @category PurchaseProductCategory
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
use WC_Order;

if ( ! class_exists( 'PurchaseProductCategory' ) ) :

	/**
	 * PurchaseProductCategory
	 *
	 * @category PurchaseProductCategory
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class PurchaseProductCategory {

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
		public $trigger = 'wc_purchase_product_category';

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
				'label'         => __( 'Product Category Purchased', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'woocommerce_checkout_order_processed',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 1,
			];

			return $triggers;
		}

		/**
		 *  Trigger listener
		 *
		 * @param int $order_id order ID.
		 * 
		 * @return void
		 */
		public function trigger_listener( $order_id ) {
			if ( ! $order_id ) {
				return;
			}
			$order = wc_get_order( $order_id );
			
			if ( ! $order instanceof WC_Order ) {
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
			
			$product_data = [];
			$category_ids = [];

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
						$cat_name[]     = $cat->name;
						$category_ids[] = $cat->term_id;
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

			if ( empty( $category_ids ) ) {
				return;
			}

			$order_data = WooCommerce::get_order_context( $order_id );

			$context = array_merge(
				$product_data,
				WordPress::get_user_context( $user_id )
			);
			if ( is_array( $order_data ) ) {
				$context = array_merge(
					$context,
					$order_data
				);
			}

			$context['total_items_in_order'] = count( $product_ids );

			foreach ( $category_ids as $category_id ) {
				$context['product_category_id'] = $category_id;
			}

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
	PurchaseProductCategory::get_instance();

endif;
