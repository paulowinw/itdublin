<?php
/**
 * AddUserToGroup.
 * php version 5.6
 *
 * @category AddUserToGroup
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
 * AddUserToGroup
 *
 * @category AddUserToGroup
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class AddUserToGroup extends AutomateAction {

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
	public $action = 'bb_add_user_to_group';

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
			'label'    => __( 'Add User to Group', 'suretriggers' ),
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
	 * @return mixed
	 * @throws Exception Exception.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		$user_id = email_exists( $selected_options['wp_user_email'] );

		if ( false === $user_id ) {
			throw new Exception( 'User with email ' . $selected_options['wp_user_email'] . ' does not exists.' );
		}
		$context    = WordPress::get_user_context( $user_id );
		$groups     = isset( $selected_options['bb_group'] ) ? $selected_options['bb_group'] : [];
		$group_id   = [];
		$group_name = [];
		if ( ! empty( $groups ) ) {
			foreach ( $groups as $group ) {
				groups_join_group( $group['value'], $user_id );
				$group_id[]   = $group['value'];
				$group_name[] = $group['label'];
			}
		}
		$context['group']      = implode( ',', $group_id );
		$context['group_name'] = implode( ',', $group_name );
		return $context;
	}
}

AddUserToGroup::get_instance();
