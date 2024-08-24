<?php
/**
 * RestrictContentRemoveUser.
 * php version 5.6
 *
 * @category RestrictContentRemoveUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\RestrictContent\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

/**
 * RestrictContentRemoveUser
 *
 * @category RestrictContentRemoveUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class RestrictContentRemoveUser extends AutomateAction {


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
	public $action = 'restrict_content_remove_user';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {

		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Remove User from Membership Level', 'suretriggers' ),
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
	 * @return array
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {

		$rcp_level_id   = (int) $selected_options['rcp_remove_levels'];
		$rcp_user_email = $selected_options['rcp_user_email'];

		$wp_user = get_user_by( 'email', $rcp_user_email );

		if ( empty( $wp_user ) ) {
			throw new Exception( 'User not found with this email address.' );
		}

		$customer = rcp_get_customer_by_user_id( $wp_user->ID );

		if ( empty( $customer ) ) {
			throw new Exception( 'Customer not found with this email address.' );
		}

		$membership_level = [];

		if ( -1 === $rcp_level_id ) {
			rcp_disable_customer_memberships( $customer->get_id() );
		} else {
			$membership_level = rcp_get_membership_level( $rcp_level_id );

			$args = [
				'customer_id' => absint( $customer->get_id() ),
				'number'      => 1,
				'orderby'     => 'id',
				'order'       => 'ASC',
				'object_id'   => $rcp_level_id,
			];

			$user_memberships = rcp_get_memberships( $args );
			if ( ! empty( $user_memberships ) ) {
				$user_memberships[0]->disable();
			}
		}

		$context = WordPress::get_user_context( $wp_user->ID );

		if ( ! empty( $membership_level ) ) {
			$context['membership_level_id']   = $rcp_level_id;
			$context['membership_level_name'] = $membership_level->get_name();
		}

		return $context;
	}
}

RestrictContentRemoveUser::get_instance();
