<?php
/**
 * FindIfUserHasActiveSubscriptionDownload.
 * php version 5.6
 *
 * @category FindIfUserHasActiveSubscriptionDownload
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\EDD\Actions;

use EDD_Customer;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\EDD\EDD;
use Exception;

/**
 * FindIfUserHasActiveSubscriptionDownload
 *
 * @category FindIfUserHasActiveSubscriptionDownload
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class FindIfUserHasActiveSubscriptionDownload extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'EDD';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'find_user_have_subscription_for_specific_download';

	use SingletonLoader;

	/**
	 * Register action.
	 *
	 * @param array $actions action data.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'User have subscription for download', 'suretriggers' ),
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
	 * @param array $selected_options selected_options.
	 * @return array|bool
	 * @throws Exception Exception.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		global $wpdb;
		if ( empty( $user_id ) ) {
			$email = $selected_options['wp_user_email'];
			$user  = get_user_by( 'email', $email );
			if ( $user ) {
				$user_id = $user->ID;
			}
		}
	
		if ( ! empty( $selected_options['download_id'] ) ) {
			$download_id = $selected_options['download_id'];
		} else {
			$download_id = 0;
		}
		if ( ! empty( $selected_options['price_id'] ) ) {
			$price_id = $selected_options['price_id'];
		} else {
			$price_id = 0;
		}
		
		if ( -1 === $price_id ) {
			$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}edd_subscriptions WHERE product_id=%d AND status='active' order by id DESC", $download_id ) );
		} else {
			$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}edd_subscriptions WHERE product_id=%d AND price_id=%d AND status='active' order by id DESC", $download_id, $price_id ) );

		}
		if ( ! class_exists( 'EDD_Customer' ) ) {
			return false;
		}
		$dynamic_response = [];
		if ( ! $result ) {
			$dynamic_response['count'] = '0';
			$dynamic_response['data']  = [];
			
		} else {
			$customer_ids = array_column( $result, 'customer_id' );
			$user_ids     = [];
			foreach ( $customer_ids as $customer_id ) {
				$customer   = new EDD_Customer( $customer_id );
				$user_ids[] = $customer->user_id;
			}
			if ( $user_id > 0 ) {
				if ( in_array( $user_id, $user_ids ) ) {
					$dynamic_response['data']  = $result;
					$dynamic_response['count'] = count( $result );
				} else {
					$dynamic_response['count'] = '0';
					$dynamic_response['data']  = [];
				}
			} else {
				$dynamic_response['data']  = $result;
				$dynamic_response['count'] = count( $result );
			}
		}
		return $dynamic_response;
	}
}

FindIfUserHasActiveSubscriptionDownload::get_instance();
