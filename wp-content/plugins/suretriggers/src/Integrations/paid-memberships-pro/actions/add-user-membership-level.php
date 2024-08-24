<?php
/**
 * AddUserMembershipLevel.
 * php version 5.6
 *
 * @category AddUserMembershipLevel
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
 * AddUserMembershipLevel
 *
 * @category AddUserMembershipLevel
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class AddUserMembershipLevel extends AutomateAction {


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
	public $action = 'add_user_to_membership_level';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Add the user to a membership level', 'suretriggers' ),
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
	 * @return array
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {

		global $wpdb;

		$membership_level = $selected_options['membership_id'];
		if ( function_exists( 'pmpro_getMembershipLevelForUser' ) ) {
			$current_level = pmpro_getMembershipLevelForUser( $user_id );
		}

		if ( ! empty( $current_level ) && absint( $current_level->ID ) == absint( $membership_level ) ) {
			$error = [
				'status'   => esc_attr__( 'Error', 'suretriggers' ),
				'response' => esc_attr__( 'User is already a member of the specified level.', 'suretriggers' ),
			];
			return $error;
		}

		$pmpro_membership_level = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->pmpro_membership_levels WHERE id = %d", $membership_level ) );

		if ( null === $pmpro_membership_level ) {
			$error = [
				'status'   => esc_attr__( 'Error', 'suretriggers' ),
				'response' => esc_attr__( 'Invalid level.', 'suretriggers' ),
			];
			return $error;
		}

		$new_level = null;
		if ( ! empty( $pmpro_membership_level->expiration_number ) ) {

			$start_date = apply_filters( 'uap_pmpro_membership_level_start_date', "'" . current_time( 'mysql' ) . "'", $user_id, $pmpro_membership_level );
			if ( property_exists( $pmpro_membership_level, 'expiration_number' ) ) {
				if ( property_exists( $pmpro_membership_level, 'expiration_period' ) ) {
					// Access the property here.
					$end_date = "'" . date_i18n( 'Y-m-d', strtotime( '+ ' . $pmpro_membership_level->expiration_number . ' ' . $pmpro_membership_level->expiration_period ) ) . "'";
					$end_date = apply_filters( 'uap_pmpro_membership_level_end_date', $end_date, $user_id, $pmpro_membership_level, $start_date );

					if ( property_exists( $pmpro_membership_level, 'id' ) ) {
						$level = [
							'user_id'         => $user_id,
							'membership_id'   => $pmpro_membership_level->id,
							'code_id'         => 0,
							'initial_payment' => 0,
							'billing_amount'  => 0,
							'cycle_number'    => 0,
							'cycle_period'    => 0,
							'billing_limit'   => 0,
							'trial_amount'    => 0,
							'trial_limit'     => 0,
							'startdate'       => $start_date,
							'enddate'         => $end_date,
						];
						if ( function_exists( 'pmpro_changeMembershipLevel' ) ) {
							$new_level = pmpro_changeMembershipLevel( $level, absint( $user_id ) );
						}
					}
				}
			}       
		} else {
			if ( function_exists( 'pmpro_changeMembershipLevel' ) ) {
				$new_level = pmpro_changeMembershipLevel( absint( $membership_level ), absint( $user_id ) );
			}
		}

		if ( true === $new_level ) {
			$response = [
				'status'   => esc_attr__( 'Success', 'suretriggers' ),
				'response' => esc_attr__( 'User added to Membership level.', 'suretriggers' ),
			];
			return $response;
		} else {
			$error = [
				'status'   => esc_attr__( 'Error', 'suretriggers' ),
				'response' => esc_attr__( "We're unable to assign the specified level to the user.", 'suretriggers' ),
			];
			return $error;
		}
	}
}

AddUserMembershipLevel::get_instance();
