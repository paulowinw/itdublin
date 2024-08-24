<?php
/**
 * RemoveUserFromGroupLeader.
 * php version 5.6
 *
 * @category RemoveUserFromGroupLeader
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\LearnDash\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Integrations\LearnDash\LearnDash;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;
use Exception;

/**
 * RemoveUserFromGroupLeader
 *
 * @category RemoveUserFromGroupLeader
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class RemoveUserFromGroupLeader extends AutomateAction {


	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'LearnDash';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'learndash_remove_user_from_group_leader';

	use SingletonLoader;

	/**
	 * Register an action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Remove User from Group Leader', 'suretriggers' ),
			'action'   => $this->action,
			'function' => [ $this, 'action_listener' ],
		];
		return $actions;
	}

	/**
	 * Action listener.
	 *
	 * @param int   $user_id user id.
	 * @param int   $automation_id automation_id.
	 * @param array $fields template fields.
	 * @param array $selected_options saved template data.
	 * @psalm-suppress UndefinedFunction
	 * @throws Exception Error.
	 * @return bool|array
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {

		$group_id = ( isset( $selected_options['groups'] ) ) ? $selected_options['groups'] : '';

		if ( ! function_exists( 'learndash_get_administrators_group_ids' ) ||
		! function_exists( 'ld_update_leader_group_access' ) ) {
			return false;
		}
		
		$all_groups_list = learndash_get_administrators_group_ids( $user_id, true );
		if ( empty( $all_groups_list ) ) {
			throw new Exception( 'The user is not a Group Leader of any group.' );
		}

		$common_groups = array_intersect( [ $group_id ], $all_groups_list );
		if ( intval( '-1' ) === intval( $group_id ) ) {
			$common_groups = $all_groups_list;
		}
		foreach ( $common_groups as $common_group_id ) {
			ld_update_leader_group_access( $user_id, $common_group_id, true );
		}

		return [
			'user'   => WordPress::get_user_context( $user_id ),
			'groups' => LearnDash::get_group_pluggable_data( $group_id ),
		];
	}

}

RemoveUserFromGroupLeader::get_instance();
