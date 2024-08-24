<?php
/**
 * MarkUserGroupLeader.
 * php version 5.6
 *
 * @category MarkUserGroupLeader
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\LearnDash\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Integrations\LearnDash\LearnDash;
use SureTriggers\Traits\SingletonLoader;

/**
 * MarkUserGroupLeader
 *
 * @category MarkUserGroupLeader
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class MarkUserGroupLeader extends AutomateAction {


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
	public $action = 'learndash_mark_user_group_leader';

	use SingletonLoader;

	/**
	 * Register an action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Mark User as Group Leader', 'suretriggers' ),
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
	 *
	 * @return bool|array
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {

		if ( ! function_exists( 'ld_update_leader_group_access' ) ) {
			return false;
		}

		$group_id                     = ( isset( $selected_options['groups'] ) ) ? $selected_options['groups'] : '';
		$group_leader_role_assignment = $selected_options['role_assignment_method'];
		$user                         = get_user_by( 'ID', $user_id );

		if ( $user && user_can( $user, 'group_leader' ) ) {
			ld_update_leader_group_access( $user_id, $group_id );
		}
		if ( $user ) {
			switch ( trim( $group_leader_role_assignment ) ) {
				case 'add':
					$user->add_role( 'group_leader' );
					ld_update_leader_group_access( $user_id, $group_id );
					break;
				case 'replace':
					$user->set_role( 'group_leader' );
					ld_update_leader_group_access( $user_id, $group_id );
					break;
			}
		}

		$user_data = LearnDash::get_user_pluggable_data( $user_id );

		return [
			'user'   => $user_data,
			'groups' => LearnDash::get_group_pluggable_data( $group_id ),
		];
	}

}

MarkUserGroupLeader::get_instance();
