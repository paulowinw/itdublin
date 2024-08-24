<?php
/**
 * UserUpdatesAccountInformation.
 * php version 5.6
 *
 * @category UserUpdatesAccountInformation
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
 * UserUpdatesAccountInformation
 *
 * @category UserUpdatesAccountInformation
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class UserUpdatesAccountInformation {

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
	public $trigger = 'wpuser_manager_user_updates_account_information';

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
			'label'         => __( 'User Updates Account Information', 'suretriggers' ),
			'action'        => $this->trigger,
			'common_action' => 'wpum_after_user_update',
			'function'      => [ $this, 'trigger_listener' ],
			'priority'      => 99,
			'accepted_args' => 3,
		];

		return $triggers;
	}

	/**
	 * Trigger listener
	 *
	 * @param object $obj Object.
	 * @param array  $values    Value.
	 * @param int    $updated_id Updated ID.
	 *
	 * @return void
	 */
	public function trigger_listener( $obj, $values, $updated_id ) {
		if ( 0 === absint( $updated_id ) ) {
			return;
		}
		
		unset( $values['account']['user_displayname'] );
		$context['user'] = $values;
		$user_info       = get_userdata( $updated_id );
		/**
		 *
		 * Ignore line
		 *
		 * @phpstan-ignore-next-line
		 */
		$context['user_account_user_displayname'] = $user_info->display_name;

		AutomationController::sure_trigger_handle_trigger(
			[
				'trigger'    => $this->trigger,
				'wp_user_id' => $updated_id,
				'context'    => $context,
			]
		);
	}
}

UserUpdatesAccountInformation::get_instance();
