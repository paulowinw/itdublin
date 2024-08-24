<?php
/**
 * RecordSaleAffiliate.
 * php version 5.6
 *
 * @category RecordSaleAffiliate
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\EasyAffiliate\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use \EasyAffiliate\Lib\ModelFactory;
use SureTriggers\Integrations\WordPress\WordPress;

/**
 * RecordSaleAffiliate
 *
 * @category RecordSaleAffiliate
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class RecordSaleAffiliate extends AutomateAction {


	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'EasyAffiliate';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'ea_record_sale_affiliate';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {

		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Record Sale for Affiliate', 'suretriggers' ),
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
	 * @return array|void
	 * @throws Exception Exception.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {

		if ( ! class_exists( 'EasyAffiliate\Models\Transaction' ) ) {
			return;
		}

		$sale                                    = [];
		$sale['referrer']                        = $selected_options['affiliate_id'];
		$sale['_wafp_transaction_cust_name']     = $selected_options['customer_name'];
		$sale['_wafp_transaction_cust_email']    = $selected_options['customer_email'];
		$sale['_wafp_transaction_item_name']     = $selected_options['product_name'];
		$sale['_wafp_transaction_trans_num']     = $selected_options['order_id'];
		$sale['_wafp_transaction_source']        = $selected_options['transaction_source'];
		$sale['_wafp_transaction_refund_amount'] = $selected_options['refund_amount'];
		$sale['_wafp_transaction_sale_amount']   = $selected_options['amount'];

		$transaction = new \EasyAffiliate\Models\Transaction();
		$transaction->load_from_sanitized_array( $sale );
		$transaction->affiliate_id = $sale['referrer'];
		$transaction->apply_refund( $sale['_wafp_transaction_refund_amount'] );

		$id = $transaction->store();
		if ( is_wp_error( $id ) ) {
			$errors[] = $id->get_error_message();
		}

		if ( ! empty( $errors ) ) {
			throw new Exception( implode( ',', $errors ) );
		}

		/**
		 *
		 * Ignore line
		 *
		 * @phpstan-ignore-next-line
		 */
		$data      = ModelFactory::fetch( 'transaction', $transaction->id );
		$affiliate = get_object_vars( $data->rec );
		if ( is_numeric( $affiliate['affiliate_id'] ) ) {
			$id = (int) $affiliate['affiliate_id'];
			return array_merge( WordPress::get_user_context( $id ), $affiliate );
		}
	}

}

RecordSaleAffiliate::get_instance();
