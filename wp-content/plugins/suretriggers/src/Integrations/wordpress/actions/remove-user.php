<?php
/**
 * RemoveUser.
 * php version 5.6
 *
 * @category RemoveUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\WordPress\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use Exception;

/**
 * RemoveUser
 *
 * @category RemoveUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class RemoveUser extends AutomateAction {

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
	public $action = 'remove_user';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'User: Remove User', 'suretriggers' ),
			'action'   => 'remove_user',
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
		
		$email = sanitize_email( $selected_options['wp_user_email'] );

		if ( is_email( $email ) ) {
			$user = get_user_by( 'email', $email );
			$data = $selected_options['delete_user_data'];
			if ( $user ) {
				require_once ABSPATH . 'wp-admin/includes/user.php';
				if ( 'yes' == $data ) {
					wp_delete_user( $user->ID );
				} else {
					/**
					 *
					 * Ignore line
					 *
					 * @phpstan-ignore-next-line
					 */
					$admin = get_user_by( 'email', get_option( 'admin_email' ) );
					/**
					 *
					 * Ignore line
					 *
					 * @phpstan-ignore-next-line
					 */
					wp_delete_user( $user->ID, $admin->ID );
				}

				$user_arr = [
					'status'   => esc_attr__( 'Success', 'suretriggers' ),
					'response' => esc_attr__( 'User deleted successfully.', 'suretriggers' ),
				];
			} else {
				$user_arr = [
					'status'   => esc_attr__( 'Error', 'suretriggers' ),
					'response' => esc_attr__( 'User not found.', 'suretriggers' ),
				];
			}
		} else {
			$user_arr = [
				'status'   => esc_attr__( 'Error', 'suretriggers' ),
				'response' => esc_attr__( 'Please enter valid email.', 'suretriggers' ),
			];
		}

		return $user_arr;
	}
}

RemoveUser::get_instance();
