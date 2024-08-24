<?php
/**
 * ChangeMembershipPlan.
 * php version 5.6
 *
 * @category ChangeMembershipPlan
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\Voxel\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;
use Exception;

/**
 * ChangeMembershipPlan
 *
 * @category ChangeMembershipPlan
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class ChangeMembershipPlan extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'Voxel';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'voxel_change_membership_plan';

	use SingletonLoader;

	/**
	 * Register action.
	 *
	 * @param array $actions action data.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Change Membership Plan', 'suretriggers' ),
			'action'   => 'voxel_change_membership_plan',
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
	 * @throws Exception Exception.
	 * 
	 * @return bool|array
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		
		$user_email = $selected_options['wp_user_email'];
		if ( is_email( $user_email ) ) {
			$user    = get_user_by( 'email', $user_email );
			$user_id = $user ? $user->ID : 1;
		}
		// Get the plan key.
		$plan_key = $selected_options['membership_plan_key'];

		if ( ! class_exists( 'Voxel\User' ) || ! class_exists( 'Voxel\Stripe' ) || ! class_exists( 'Voxel\Plan' ) ) {
			return false;
		}

		// If price id is provided.
		$price_id   = isset( $selected_options['price_id'] ) ? $selected_options['price_id'] : '';
		$price_type = 'payment';

		if ( '' !== $price_id ) {
			$stripe     = \Voxel\Stripe::getClient();
			$price      = $stripe->prices->retrieve( $price_id );
			$price_type = 'recurring' === $price->type ? 'subscription' : 'payment';
		}

		// Get the user.
		$voxel_user = \Voxel\User::get( $user_id );
		if ( ! $voxel_user ) {
			throw new Exception( 'User not found' );
		}

		// Get the plan.
		$plan = \Voxel\Plan::get( $plan_key );
		if ( ! $plan ) {
			throw new Exception( 'Plan not found' );
		}

		// Check if user has at least one role that supports chosen plan.
		if ( ! $plan->supports_user( $voxel_user ) ) {
			throw new Exception( "This plan is not supported for the specified user's role" );
		}

		// Change the plan.
		$meta_key = \Voxel\Stripe::is_test_mode() ? 'voxel:test_plan' : 'voxel:plan';
		update_user_meta(
			$voxel_user->get_id(),
			$meta_key,
			wp_slash(
				wp_json_encode(
					[
						'plan'     => $plan_key,
						'price_id' => $price_id,
						'type'     => $price_type,
						'status'   => 'active',
						'metadata' => [
							'voxel:payment_for'       => 'membership',
							'voxel:plan'              => $plan_key,
							'voxel:limits'            => wp_json_encode( [] ),
							'voxel:original_price_id' => $price_id,
						],
					]
				)
			)
		);

		do_action( 'voxel/membership/pricing-plan-updated', $voxel_user, $voxel_user->get_membership(), $voxel_user->get_membership( $refresh_cache = true ) ); // @phpcs:ignore
		return [
			'success' => true,
			'message' => esc_attr__( 'Membership plan updated successfully', 'suretriggers' ),
			'user_id' => $user_id,
			'plan'    => $plan_key,
		];
	}

}

ChangeMembershipPlan::get_instance();
