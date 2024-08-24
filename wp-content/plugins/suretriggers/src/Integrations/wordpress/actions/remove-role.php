<?php
/**
 * RemoveRole.
 * php version 5.6
 *
 * @category RemoveRole
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\Wordpress\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use Exception;
use WP_User;

/**
 * RemoveRole
 *
 * @category RemoveRole
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class RemoveRole extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'WordPress';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'remove_role';

	use SingletonLoader;

	/**
	 * Register action.
	 *
	 * @param array $actions action data.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Role: Remove a role from the user', 'suretriggers' ),
			'action'   => 'remove_role',
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
	 * @return bool
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		$field = reset( $fields );
		$user  = new WP_User( $user_id );
		if ( ! $user instanceof WP_User ) {
			throw new Exception( 'This user is not type of WP_User' );
		}

		$user->remove_role( $selected_options[ $field['name'] ] );
		return true;
	}

}

RemoveRole::get_instance();
