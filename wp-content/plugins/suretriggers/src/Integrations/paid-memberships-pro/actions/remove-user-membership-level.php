<?php
/**
 * RemoveUserMembershipLevel.
 * php version 5.6
 *
 * @category RemoveUserMembershipLevel
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\PaidMembershipsPro\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;

/**
 * RemoveUserMembershipLevel
 *
 * @category RemoveUserMembershipLevel
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class RemoveUserMembershipLevel extends AutomateAction {


	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'PaidMembershipsPro';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'remove_user_from_membership_level';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Remove the user from a membership level', 'suretriggers' ),
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
	 *
	 * @return array|void
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {

		$membership_level = $selected_options['membership_id'];

		if ( ! function_exists( 'pmpro_getMembershipLevelsForUser' ) ) {
			return;
		}

		$user_membership_levels = pmpro_getMembershipLevelsForUser( $user_id );

		// Convert result into simple array.
		$user_membership_levels = array_map(
			function ( $membership_level ) {
				return $membership_level->ID;
			},
			$user_membership_levels
		);

		// Do this for 'Any' selection.
		if ( intval( '-1' ) === intval( $membership_level ) ) {

			// Check if user has any membership levels first. Complete with error if does not have.
			if ( empty( $user_membership_levels ) ) {
				$error = [
					'status'   => esc_attr__( 'Error', 'suretriggers' ),
					'response' => esc_attr__( 'User does not belong to any membership levels.', 'suretriggers' ),
				];
				return $error;
			}

			// Delete all membership leverls.
			foreach ( $user_membership_levels as $membership_level ) {
				if ( function_exists( 'pmpro_cancelMembershipLevel' ) ) {
					$cancel_level = pmpro_cancelMembershipLevel( absint( $membership_level ), absint( $user_id ) );
				}
			}

			$response = [
				'status'   => esc_attr__( 'Success', 'suretriggers' ),
				'response' => esc_attr__( 'User removed from Membership level.', 'suretriggers' ),
			];
			return $response;

		}

		// Otherwise, remove specific membership level.
		if ( ! in_array( $membership_level, $user_membership_levels, true ) ) {
			// Complete with error if the user was not a member of the specified level.
			$error = [
				'status'   => esc_attr__( 'Error', 'suretriggers' ),
				'response' => esc_attr__( 'User was not a member of the specified level.', 'suretriggers' ),
			];
			return $error;
		}

		// Try removing user membership level.
		if ( function_exists( 'pmpro_cancelMembershipLevel' ) ) {
			if ( pmpro_cancelMembershipLevel( absint( $membership_level ), absint( $user_id ) ) ) {
				$response = [
					'status'   => esc_attr__( 'Success', 'suretriggers' ),
					'response' => esc_attr__( 'User removed from Membership level.', 'suretriggers' ),
				];
				return $response;
			} else {
				$error = [
					'status'   => esc_attr__( 'Error', 'suretriggers' ),
					'response' => esc_attr__( "We're unable to cancel the specified level from the user.", 'suretriggers' ),
				];
				return $error;
			}
		} else {
			$error = [
				'status'   => esc_attr__( 'Error', 'suretriggers' ),
				'response' => esc_attr__( "We're unable to cancel the specified level from the user.", 'suretriggers' ),
			];
			return $error;
		}
	}
}

RemoveUserMembershipLevel::get_instance();
