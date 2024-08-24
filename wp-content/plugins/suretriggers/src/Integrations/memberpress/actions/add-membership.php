<?php
/**
 * AddMembership.
 * php version 5.6
 *
 * @category AddMembership
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\MemberPress\Actions;

use Exception;
use MeprEvent;
use MeprProduct;
use MeprTransaction;
use MeprUser;
use MeprUtils;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;

/**
 * AddMembership
 *
 * @category AddMembership
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class AddMembership extends AutomateAction {


	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'MemberPress';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'mp_add_membership';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Add to Membership', 'suretriggers' ),
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
	 * @throws Exception Throws exception.
	 *
	 * @return array
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {

		if ( ! $user_id ) {
			throw new Exception( 'User not found with this email address.' );
		}

		if ( is_array( $selected_options['memberpressproduct'] ) ) {
			$product_id = $selected_options['memberpressproduct']['value'];
		} else {
			$product_id = $selected_options['memberpressproduct'];
		}
		$sub_total       = ( isset( $selected_options['subtotal'] ) ) ? $selected_options['subtotal'] : '';
		$tax_amount      = ( isset( $selected_options['taxAmount'] ) ) ? $selected_options['taxAmount'] : '';
		$tax_rate        = ( isset( $selected_options['taxRate'] ) ) ? $selected_options['taxRate'] : '';
		$tnx_status      = ( isset( $selected_options['status'] ) ) ? $selected_options['status'] : '';
		$gateway         = ( isset( $selected_options['gateway'] ) ) ? $selected_options['gateway'] : '';
		$expiration_date = ( isset( $selected_options['expireDate'] ) ) ? $selected_options['expireDate'] : '';

		$txn  = new MeprTransaction();
		$user = new MeprUser();
		$user->load_user_data_by_id( $user_id );

		$txn->trans_num  = uniqid( 'st-mp', true );
		$txn->user_id    = $user->ID;
		$txn->product_id = sanitize_key( $product_id );

		$txn->amount     = (float) $sub_total;
		$txn->tax_amount = (float) $tax_amount;
		$txn->total      = ( (float) $sub_total + (float) $tax_amount );
		$txn->tax_rate   = (float) $tax_rate;
		$txn->status     = sanitize_text_field( $tnx_status );
		$txn->gateway    = sanitize_text_field( $gateway );
		if ( ! class_exists( 'MeprUtils' ) ) {
			throw new Exception( 'MemberPress plugin is not installed' );
		}
		$txn->created_at = MeprUtils::ts_to_mysql_date( time() );

		if ( isset( $expiration_date ) && ( '' === $expiration_date || is_null( $expiration_date ) ) ) {
			$obj           = new MeprProduct( sanitize_key( $product_id ) );
			$expires_at_ts = $obj->get_expires_at();
			if ( is_null( $expires_at_ts ) ) {
				$txn->expires_at = MeprUtils::db_lifetime();
			} else {
				$txn->expires_at = MeprUtils::ts_to_mysql_date( $expires_at_ts, 'Y-m-d 23:59:59' );
			}
		} else {
			$txn->expires_at = MeprUtils::ts_to_mysql_date( strtotime( $expiration_date ), 'Y-m-d 23:59:59' );
		}

		$txn->store();

		if ( MeprTransaction::$complete_str === $txn->status ) {
			MeprEvent::record( 'transaction-completed', $txn );
			$sub = $txn->subscription();
			// This is a recurring payment.
			if ( $sub && $sub->txn_count > 1 ) {
				MeprEvent::record(
					'recurring-transaction-completed',
					$txn
				);
			} elseif ( ! $sub ) {
				MeprEvent::record(
					'non-recurring-transaction-completed',
					$txn
				);
			}
			
			MeprUtils::send_signup_notices( $txn );
			
		}

		return [
			'user_email'                    => $selected_options['wp_user_email'],
			'membership_id'                 => $product_id,
			'membership_title'              => get_the_title( $product_id ),
			'membership_url'                => get_permalink( $product_id ),
			'membership_featured_image_id'  => get_post_meta( $product_id, '_thumbnail_id', true ),
			'membership_featured_image_url' => get_the_post_thumbnail_url( $product_id ),
			'sub_total'                     => $sub_total,
			'tax_amount'                    => $tax_amount,
			'tax_rate'                      => $tax_rate,
			'transaction_status'            => $tnx_status,
			'gateway'                       => $gateway,
			'expiration_date'               => $expiration_date,
		];
	}
}

AddMembership::get_instance();
