<?php
/**
 * RemoveStoreCreditFromUser.
 * php version 5.6
 *
 * @category RemoveStoreCreditFromUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\AdvancedCoupons\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Integrations\WordPress\WordPress;
use ACFWF\Models\Objects\Store_Credit_Entry;
use ACFWF\Helpers\Plugin_Constants;
use SureTriggers\Traits\SingletonLoader;

/**
 * RemoveStoreCreditFromUser
 *
 * @category RemoveStoreCreditFromUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class RemoveStoreCreditFromUser extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'AdvancedCoupons';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'remove_store_credit_from_user';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Remove Store Credit from User', 'suretriggers' ),
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
	 * @throws Exception Exception.
	 *
	 * @return bool|array|void 
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		$amount = floatval( $selected_options['credit_amount'] );

		$user_id = $selected_options['wp_user_email'];

		if ( is_email( $user_id ) ) {
			$user = get_user_by( 'email', $user_id );

			if ( $user ) {
				$user_id = $user->ID;
				if ( $amount <= 0 ) {
					return false;
				}

				if ( function_exists( 'ACFWF' ) ) {
					$balance = apply_filters( 'acfw_filter_amount', \ACFWF()->Store_Credits_Calculate->get_customer_balance( $user_id ) );

					if ( $balance < $amount ) {
						throw new Exception( "The user's store credit balance is insufficient." );
					}
				}
		
				$params = [
					'user_id'   => $user_id,
					'type'      => 'decrease',
					'amount'    => $amount,
					'object_id' => $user_id,
					'action'    => 'admin_decrease',
					'date'      => gmdate( 'Y-m-d H:i:s' ),
					'note'      => 'SureTriggers',
				];
		
				$date_format = 'Y-m-d H:i:s';
		
				if ( class_exists( 'ACFWF\Models\Objects\Store_Credit_Entry' ) ) {
					$store_credit_entry = new \ACFWF\Models\Objects\Store_Credit_Entry();
			
					foreach ( $params as $prop => $value ) {
						if ( $value && 'date' === $prop ) {
							$store_credit_entry->set_date_prop( $prop, $value, $date_format );
						} else {
							$store_credit_entry->set_prop( $prop, $value );
						}
			
						if ( 'action' === $prop && in_array(
							$value,
							[
								'admin_increase',
								'admin_decrease',
							],
							true
						) ) {
							$store_credit_entry->set_prop( 'object_id', $user_id );
						}
					}
					$check = $store_credit_entry->save();
					if ( is_wp_error( $check ) ) {
						throw new Exception( 'The amount enter is not valid.' );
					}
					if ( function_exists( 'ACFWF' ) ) {
						$cur_balance = apply_filters( 'acfw_filter_amount', \ACFWF()->Store_Credits_Calculate->get_customer_balance( $user_id ) );

						$context['current_balance'] = $cur_balance;
						return array_merge(
							WordPress::get_user_context( $user_id ),
							$context
						);
					}
				}
			} else {
				throw new Exception( 'Invalid User.' );
			}
		} else {
			throw new Exception( 'Please enter valid email address.' );
		}
	}
}

RemoveStoreCreditFromUser::get_instance();
