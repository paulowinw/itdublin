<?php
/**
 * RemoveUserFromGroup.
 * php version 5.6
 *
 * @category RemoveUserFromGroup
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\BuddyBoss\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

/**
 * RemoveUserFromGroup
 *
 * @category RemoveUserFromGroup
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class RemoveUserFromGroup extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'BuddyBoss';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'bb_remove_user_from_group';

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
			'label'    => __( 'Remove User from Group', 'suretriggers' ),
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
	 * @return array
	 * @throws Exception Exception.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		if ( empty( $selected_options['remove_user'] ) || ! is_email( $selected_options['remove_user'] ) ) {
			throw new Exception( 'Invalid email.' );
		}

		$user_id = email_exists( $selected_options['remove_user'] );

		if ( false === $user_id ) {
			throw new Exception( 'User with email ' . $selected_options['remove_user'] . ' does not exists .' );
		}

		$groups  = [];
		$context = WordPress::get_user_context( $user_id );
		if ( 'all' === $selected_options['bb_group'] ) {
			$all_groups = groups_get_groups();
			if ( isset( $all_groups['groups'] ) && ! empty( $all_groups['groups'] ) ) {
				foreach ( $all_groups['groups'] as $group ) {
					$groups[] = $group->id;
				}
			}
		} else {
			$groups[] = $selected_options['bb_group'];
		}

		if ( ! empty( $groups ) ) {
			foreach ( $groups as $group_id ) {
				$is_member = groups_is_user_member( $user_id, $group_id );
				if ( $is_member ) {
					groups_leave_group( $group_id, $user_id );
				}
			}
		}

		return $context;
	}
}

RemoveUserFromGroup::get_instance();
