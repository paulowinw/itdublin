<?php
/**
 * ChangeUserMembershipPlan.
 * php version 5.6
 *
 * @category ChangeUserMembershipPlan
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\WoocommerceMemberships\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use WC_REST_Exception;
use Exception;

/**
 * ChangeUserMembershipPlan
 *
 * @category ChangeUserMembershipPlan
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class ChangeUserMembershipPlan extends AutomateAction {

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
	public $action = 'wc_change_user_membership_plan';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Change User Membership Plan', 'suretriggers' ),
			'action'   => 'wc_change_user_membership_plan',
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
	 * @throws Exception Exception.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		
		$existing_plan_id = $selected_options['existing_membership_plan'];
		$new_plan         = $selected_options['new_membership_plan'];
		$user_id          = $selected_options['wp_user_email'];

		if ( ! is_email( $user_id ) ) {
			throw new Exception( 'The user email is not valid.' );
		}

		$user = get_user_by( 'email', $user_id );

		if ( ! $user ) {
			throw new Exception( 'This user is not registered.' );
		}

		$user_id = $user->ID;

		if ( ! function_exists( 'wc_memberships_get_user_membership' ) ) {
			return;
		}

		$check_for_membership = wc_memberships_get_user_membership( $user_id, $new_plan );
		if ( $check_for_membership ) {
			throw new Exception( 'Plan could not be changed or created because the user is using the plan.' );
		} else {
			$membership = $existing_plan_id ? wc_memberships_get_user_membership( $user_id, $existing_plan_id ) : false;
			if ( $membership ) {
				wp_update_post(
					[
						'ID'          => $membership->get_id(),
						'post_parent' => $new_plan,
					]
				);
				return wc_memberships_get_user_membership( $user_id, $new_plan );
			} else {
				// if no existing plan and allow create is checked, create a new plan for the user.
				try {
					$arguments = [
						'plan_id' => $new_plan,
						'user_id' => $user_id,
					];
					if ( function_exists( 'wc_memberships_create_user_membership' ) ) {
						return wc_memberships_create_user_membership( $arguments );
					}
				} catch ( WC_REST_Exception $e ) {
					throw new Exception( $e->getMessage() );
				}
			}
		}
	}
}

ChangeUserMembershipPlan::get_instance();
