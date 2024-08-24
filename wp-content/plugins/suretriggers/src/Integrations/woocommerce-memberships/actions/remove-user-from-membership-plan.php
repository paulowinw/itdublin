<?php
/**
 * RemoveUserFromMembershipPlan.
 * php version 5.6
 *
 * @category RemoveUserFromMembershipPlan
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\WoocommerceMemberships\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;

/**
 * RemoveUserFromMembershipPlan
 *
 * @category RemoveUserFromMembershipPlan
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class RemoveUserFromMembershipPlan extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'WoocommerceMemberships';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'wc_remove_user_from_membership_plan';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Remove the user from a membership plan.', 'suretriggers' ),
			'action'   => 'wc_remove_user_from_membership_plan',
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
	 *
	 * @return void|array|bool
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		
		$plan    = $selected_options['membership_plan'];
		$user_id = $selected_options['wp_user_email'];

		if ( is_email( $user_id ) ) {
			$user = get_user_by( 'email', $user_id );
			if ( $user ) {
				$user_id = $user->ID;
				if ( '-1' == $plan ) {
					if ( function_exists( 'wc_memberships_get_user_memberships' ) ) {
						$user_all_memberships = wc_memberships_get_user_memberships( $user_id );
					}
					if ( empty( $user_all_memberships ) ) {
						$error = [
							'status'   => esc_attr__( 'Error', 'suretriggers' ),
							'response' => esc_attr__( 'The user was not a member of any membership plans.', 'suretriggers' ),
						];
						return $error;
					} else {
						try {
							foreach ( $user_all_memberships as $membership ) {
								wp_delete_post( $membership->post->ID );
							}
							return $user_all_memberships;
						} catch ( \Exception $e ) {
							$error_message = $e->getMessage();
							$error         = [
								'status'   => esc_attr__( 'Error', 'suretriggers' ),
								'response' => $error_message,
							];
							return $error;
						}
					}
				} else {
					if ( function_exists( 'wc_memberships_is_user_member' ) ) {
						$check_membership_plan = wc_memberships_is_user_member( $user_id, $plan );
						if ( true !== $check_membership_plan ) {
							$error = [
								'status'   => esc_attr__( 'Error', 'suretriggers' ),
								'response' => esc_attr__( 'The user was not a member of the specified membership plan.', 'suretriggers' ),
							];
							return $error;
						} else {
							try {
								if ( function_exists( 'wc_memberships_get_user_membership' ) ) {
									$user_membership = wc_memberships_get_user_membership( $user_id, $plan );
									wp_delete_post( $user_membership->post->ID );
									return $user_membership;
								}
							} catch ( \Exception $e ) {
								$error_message = $e->getMessage();
								$error         = [
									'status'   => esc_attr__( 'Error', 'suretriggers' ),
									'response' => $error_message,
								];
								return $error;
							}
						}
					}
				}
			} else {
				$error = [
					'status'   => esc_attr__( 'Error', 'suretriggers' ),
					'response' => esc_attr__( 'This user is not registered.', 'suretriggers' ),
				];
				return $error;
			}
		}
	}
}

RemoveUserFromMembershipPlan::get_instance();
