<?php
/**
 * UserRemovesProfilePhoto.
 * php version 5.6
 *
 * @category UserRemovesProfilePhoto
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\WPUserManager\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

/**
 * UserRemovesProfilePhoto
 *
 * @category UserRemovesProfilePhoto
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class UserRemovesProfilePhoto {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'WPUserManager';

	/**
	 * Trigger name.
	 *
	 * @var string
	 */
	public $trigger = 'wpuser_manager_user_removes_profile_photo';

	use SingletonLoader;

	/**
	 * Constructor
	 *
	 * @since  1.0.0
	 */
	public function __construct() {
		add_filter( 'sure_trigger_register_trigger', [ $this, 'register' ] );
	}

	/**
	 * Register action.
	 *
	 * @param array $triggers trigger data.
	 *
	 * @return array
	 */
	public function register( $triggers ) {

		$triggers[ $this->integration ][ $this->trigger ] = [
			'label'         => __( 'User Removes Profile Photo', 'suretriggers' ),
			'action'        => $this->trigger,
			'common_action' => 'wpum_user_update_remove_avatar',
			'function'      => [ $this, 'trigger_listener' ],
			'priority'      => 99,
			'accepted_args' => 1,
		];

		return $triggers;
	}

	/**
	 * Trigger listener
	 *
	 * @param int $user_id User ID.
	 *
	 * @return void
	 */
	public function trigger_listener( $user_id ) {
		if ( 0 === absint( $user_id ) ) {
			return;
		}
		
		$context['user_id'] = WordPress::get_user_context( $user_id );

		AutomationController::sure_trigger_handle_trigger(
			[
				'trigger'    => $this->trigger,
				'wp_user_id' => $user_id,
				'context'    => $context,
			]
		);
	}
}

UserRemovesProfilePhoto::get_instance();
