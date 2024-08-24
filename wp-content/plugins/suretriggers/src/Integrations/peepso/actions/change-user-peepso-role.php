<?php
/**
 * ChangeUserPeepsoRole.
 * php version 5.6
 *
 * @category ChangeUserPeepsoRole
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\PeepSo\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use Exception;
use PeepSo;
use PeepSoUser;
use SureTriggers\Integrations\WordPress\WordPress;

/**
 * ChangeUserPeepsoRole
 *
 * @category ChangeUserPeepsoRole
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class ChangeUserPeepsoRole extends AutomateAction {


	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'PeepSo';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'change_user_peepso_role';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Change User PeepSo Role', 'suretriggers' ),
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
	 *
	 * @return array
	 * @throws Exception Exception.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {

		if ( ! class_exists( 'PeepSoUser' ) || ! class_exists( 'PeepSo' ) ) {
			return [];
		}
		$new_user_role = $selected_options['new_role'];
		$system_roles  = [
			'member'   => 'Community Member',
			'admin'    => 'Community Administrator',
			'ban'      => 'Banned',
			'register' => 'Pending user email verification',
			'verified' => 'Pending admin approval',
		];

		if ( empty( $new_user_role ) || ! isset( $system_roles[ $new_user_role ] ) ) {
			throw new Exception( "The selected role doesn't exist" );
		}

		$user = PeepSoUser::get_instance( $user_id );

		if ( 0 === $user_id || null === $user->get_id() ) {
			throw new Exception( 'Invalid User' );
		}

		// Don't allow banning administrators.
		if ( PeepSo::is_admin( $user_id ) && 'ban' === $new_user_role ) {
			throw new Exception( 'You cannot ban administrators.' );
		}

		$user->approve_user();
		$user->set_user_role( $new_user_role );
		return WordPress::get_user_context( $user_id );
	}
}

ChangeUserPeepsoRole::get_instance();
