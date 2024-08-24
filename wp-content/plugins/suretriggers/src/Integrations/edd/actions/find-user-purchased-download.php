<?php
/**
 * FindIfUserPurchasedDownload.
 * php version 5.6
 *
 * @category FindIfUserPurchasedDownload
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\EDD\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\EDD\EDD;
use Exception;

/**
 * FindIfUserPurchasedDownload
 *
 * @category FindIfUserPurchasedDownload
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class FindIfUserPurchasedDownload extends AutomateAction {

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
	public $action = 'find_user_purchased_download';

	use SingletonLoader;

	/**
	 * Register action.
	 *
	 * @param array $actions action data.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'User has purchased a download', 'suretriggers' ),
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
		if ( $download_id > 0 ) {
			$args = [
				'download' => $download_id,
				'user'     => $user_id,
				'output'   => 'payments',
				
			];
		} else {
			$args = [
				'user' => $user_id,
			];
		}
		
		
		if ( ! function_exists( 'edd_get_payments' ) ) {
			return false;
		}
		$payments         = edd_get_payments( $args );
		$dynamic_response = [];
		if ( ! $payments ) {
			$dynamic_response['count'] = '0';
			$dynamic_response['data']  = [];
			
		} else {
			$data      = (array) EDD::get_all_product_purchase_context( $payments, 'edd_action', $download_id );
			$price_ids = array_column( $data, 'price_id' );
		
			if ( $price_id > 0 ) {
				if ( in_array( $price_id, $price_ids ) ) {
					$dynamic_response['data']  = $data;
					$dynamic_response['count'] = count( $data );
				} else {
					$dynamic_response['count'] = '0';
					$dynamic_response['data']  = [];
				}
			} else {
				$dynamic_response['data']  = $data;
				$dynamic_response['count'] = count( $data );
			}
		}
		
		return $dynamic_response;
	}
}

FindIfUserPurchasedDownload::get_instance();
