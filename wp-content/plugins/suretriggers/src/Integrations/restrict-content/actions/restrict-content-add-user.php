<?php
/**
 * RestrictContentAddUser.
 * php version 5.6
 *
 * @category RestrictContentAddUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\RestrictContent\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Integrations\RestrictContent\RestrictContent;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

/**
 * RestrictContentAddUser
 *
 * @category RestrictContentAddUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class RestrictContentAddUser extends AutomateAction {


	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'RestrictContent';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'restrict_content_add_user';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 *
	 * @return array
	 */
	public function register( $actions ) {

		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Add User to Membership Level', 'suretriggers' ),
			'action'   => $this->action,
			'function' => [ $this, '_action_listener' ],
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
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {

		$rcp_level_id    = $selected_options['rcp_levels']['value'];
		$rcp_user_email  = $selected_options['rcp_user_email'];
		$rcp_expiry_date = $selected_options['rcp_expiry_date'];

		if ( empty( $rcp_level_id ) ) {
			return false;
		}

		if ( empty( $rcp_user_email ) ) {
			return false;
		}

		$newest_time  = strtotime( current_time( 'mysql' ) );
		$created_date = gmdate( 'Y-m-d H:i:s', $newest_time );

		$wp_user = get_user_by( 'email', $rcp_user_email );

		$customer      = [];
		$customer_args = [
			'date_registered' => $created_date,
		];

		if ( ! empty( $wp_user ) ) {
			$customer = rcp_get_customer_by_user_id( $wp_user->ID );

			if ( empty( $customer ) ) {
				$customer_args['user_id'] = $wp_user->ID;
			}
		} else {
			$customer_args['user_args'] = [
				'user_login' => sanitize_text_field( $rcp_user_email ),
				'user_email' => sanitize_text_field( $rcp_user_email ),
				'user_pass'  => wp_generate_password(),
			];
		}

		// Create a new customer record if one does not exist.
		if ( empty( $customer ) ) {
			$customer_id = rcp_add_customer( $customer_args );
		} else {
			$customer_id = $customer->get_id();
		}

		$customer = rcp_get_customer( $customer_id );

		$status          = 'active';
		$membership_args = [
			'customer_id'      => $customer->get_id(),
			'user_id'          => $customer->get_user_id(),
			'object_id'        => $rcp_level_id,
			'status'           => $status,
			'created_date'     => $created_date,
			'gateway'          => 'manual',
			'subscription_key' => rcp_generate_subscription_key(),
		];
		if ( ! empty( $rcp_expiry_date ) ) {
			$membership_args['expiration_date'] = gmdate( 'Y-m-d H:i:s', strtotime( $rcp_expiry_date ) );
		}

		$membership_id = rcp_add_membership( $membership_args );
		$membership    = rcp_get_membership( $membership_id );

		return array_merge(
			WordPress::get_user_context( $customer->get_user_id() ),
			RestrictContent::get_rcp_membership_detail_context( $membership )
		);
	}
}

RestrictContentAddUser::get_instance();
