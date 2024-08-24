<?php
/**
 * AddAffiliate.
 * php version 5.6
 *
 * @category AddAffiliate
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

/**
 * AddAffiliate
 *
 * @category AddAffiliate
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class AddAffiliate extends AutomateAction {


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
	public $action = 'ea_add_affiliate';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {

		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Add Affiliate', 'suretriggers' ),
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

		if ( ! class_exists( 'EasyAffiliate\Models\User' ) ) {
			return;
		}

		$aff                          = [];   
		$aff['first_name']            = $selected_options['first_name'];
		$aff['last_name']             = $selected_options['last_name'];
		$aff['_wafp_user_user_login'] = $selected_options['_wafp_user_user_login'];
		$aff['_wafp_user_user_email'] = sanitize_email( $selected_options['_wafp_user_user_email'] );
		$aff['wafp_paypal_email']     = $selected_options['wafp_paypal_email'];
		$aff['_wafp_user_user_pass']  = $selected_options['_wafp_user_user_pass'];
		$aff['wafp_user_address_one'] = $selected_options['wafp_user_address_one'];
		$aff['wafp_user_address_two'] = $selected_options['wafp_user_address_two'];
		$aff['wafp_user_city']        = $selected_options['wafp_user_city'];
		$aff['wafp_user_state']       = $selected_options['wafp_user_state'];
		$aff['wafp_user_zip']         = $selected_options['wafp_user_zip'];
		$aff['wafp_user_country']     = $selected_options['wafp_user_country'];
		$notification                 = $selected_options['notification'];
		
		$notification = ( 'true' === $notification ) ? true : false;

		$user    = new \EasyAffiliate\Models\User();
		$wp_user = get_user_by( 'email', $aff['_wafp_user_user_email'] );
		if ( $wp_user ) {
			$is_user_affiliate = get_user_meta( $wp_user->ID, 'wafp_is_affiliate', true );
			if ( isset( $is_user_affiliate ) && true === $is_user_affiliate ) {
				throw new Exception( 'The user is already an affiliate.' );
			}
			$user->rec->ID = $wp_user->ID;
		}

		$user->load_from_sanitized_array( $aff );
		$user->is_affiliate = true;
		$user->store();
		do_action( 'esaf-process-signup', $user ); // @phpcs:ignore
		$user->send_account_notifications( true, $notification );
		$data = new \EasyAffiliate\Models\User( $user->rec->ID );
		return get_object_vars( $data->rec );
	}

}

AddAffiliate::get_instance();
