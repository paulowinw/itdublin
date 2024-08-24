<?php
/**
 * UserUpdatesAvatar.
 * php version 5.6
 *
 * @category UserUpdatesAvatar
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\PeepSo\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;

/**
 * UserUpdatesAvatar
 *
 * @category UserUpdatesAvatar
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class UserUpdatesAvatar {


	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'PeepSo';

	/**
	 * Trigger name.
	 *
	 * @var string
	 */
	public $trigger = 'peepso_user_updates_avatar';

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
	 * @return array
	 */
	public function register( $triggers ) {

		$triggers[ $this->integration ][ $this->trigger ] = [
			'label'         => __( 'User Updates Avatar', 'suretriggers' ),
			'action'        => $this->trigger,
			'common_action' => 'peepso_user_after_change_avatar',
			'function'      => [ $this, 'trigger_listener' ],
			'priority'      => 10,
			'accepted_args' => 4,
		];

		return $triggers;
	}

	/**
	 * Trigger listener
	 *
	 * @param int $user_id User ID.
	 * @param int $dest_thumb Dest Thumb.
	 * @param int $dest_full Dest Full.
	 * @param int $dest_orig Dest Org.
	 *
	 * @return void
	 */
	public function trigger_listener( $user_id, $dest_thumb, $dest_full, $dest_orig ) {
		if ( ! class_exists( 'PeepSoUser' ) ) {
			return;
		}
		if ( empty( $user_id ) ) {
			return;
		}
		$context['user'] = WordPress::get_user_context( $user_id );

		AutomationController::sure_trigger_handle_trigger(
			[
				'trigger' => $this->trigger,
				'context' => $context,
			]
		);
	}

}

UserUpdatesAvatar::get_instance();
