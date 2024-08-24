<?php
/**
 * AddUserRole.
 * php version 5.6
 *
 * @category AddUserRole
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;

/**
 * AddUserRole
 *
 * @category AddUserRole
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class AddUserRole extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'UltimateMember';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'um_add_user_role';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( "Add a role to the user's roles", 'suretriggers' ),
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
	 * @psalm-suppress UndefinedMethod
	 * @throws Exception Exception.
	 * 
	 * @return array|bool|void
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		$field = reset( $fields );
		$user  = new WP_User( $user_id );

		if ( ! ( $user instanceof WP_User ) ) {
			$this->set_error(
				[
					'wp_user_id' => $user_id,
					'msg'        => __( 'This user is not type of WP_User', 'suretriggers' ),
				]
			);
			return false;
		}
		$user->add_role( $selected_options[ $field['name'] ] );

		return true;
	}
}

AddUserRole::get_instance();
