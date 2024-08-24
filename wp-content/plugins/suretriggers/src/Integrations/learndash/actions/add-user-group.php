<?php
/**
 * AddUserGroup.
 * php version 5.6
 *
 * @category AddUserGroup
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
use WP_Query;

/**
 * AddUserGroup
 *
 * @category AddUserGroup
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class AddUserGroup extends AutomateAction {


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
	public $action = 'learndash_add_user_group';

	use SingletonLoader;

	/**
	 * Register an action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Add user to a group', 'suretriggers' ),
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

		if ( ! $user_id ) {
			$this->set_error(
				[
					'msg' => __( 'User Not found', 'suretriggers' ),
				]
			);
			return false;
		}

		$group_id = ( isset( $selected_options['groups'] ) ) ? $selected_options['groups'] : '';

		// Adding to all groups.
		if ( 'all' === $group_id ) {
			// Get all groups.
			$query  = new WP_Query(
				[
					'post_type'   => 'groups',
					'post_status' => 'publish',
					'fields'      => 'ids',
					'nopaging'    => true, //phpcs:ignore
				]
			);
			$groups = $query->get_posts();
		} else {
			$group = get_post( (int) $group_id );

			// Bail if group doesn't exists.
			if ( ! $group ) {
				$this->set_error(
					[
						'msg' => __( 'No group is available ', 'suretriggers' ),
					]
				);
				return false;
			}

			$groups = [ $group_id ];
		}

		$added_to_groups = [];

		// Add user to groups.
		$count = 1;
		foreach ( $groups as $group_id ) {
			ld_update_group_access( $user_id, $group_id );
			$arr_key                     = count( $groups ) > 1 ? 'group_' . $count : 'group';
			$added_to_groups[ $arr_key ] = LearnDash::get_group_pluggable_data( $group_id );
			$count++;
		}

		$user_data = LearnDash::get_user_pluggable_data( $user_id );

		return [
			'user'   => $user_data,
			'groups' => $added_to_groups,
		];
	}

}

AddUserGroup::get_instance();
