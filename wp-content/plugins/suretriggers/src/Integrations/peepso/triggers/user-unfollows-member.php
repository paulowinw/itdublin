<?php
/**
 * UserUnfollowsMember.
 * php version 5.6
 *
 * @category UserUnfollowsMember
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\PeepSo\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

/**
 * UserUnfollowsMember
 *
 * @category UserUnfollowsMember
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class UserUnfollowsMember {


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
	public $trigger = 'peepso_user_unfollows_member';

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
			'label'         => __( 'User Unfollows PeppSo Member', 'suretriggers' ),
			'action'        => $this->trigger,
			'common_action' => 'peepso_ajax_start',
			'function'      => [ $this, 'trigger_listener' ],
			'priority'      => 10,
			'accepted_args' => 1,
		];

		return $triggers;
	}

	/**
	 * Trigger listener
	 *
	 * @param array $data Data.
	 *
	 * @return void
	 */
	public function trigger_listener( $data ) {
		$post_data = $_POST; // @codingStandardsIgnoreLine
		if ( 'followerajax.set_follow_status' !== $data ) {
			return;
		}

		$follower_id = $post_data['uid'];
		$user_id     = $post_data['user_id'];

		if ( false === $follower_id ) {
			return;
		}

		$context['follower_user']  = WordPress::get_user_context( $user_id );
		$context['following_user'] = WordPress::get_user_context( $follower_id );
		$context['follow_user_id'] = $follower_id;

		AutomationController::sure_trigger_handle_trigger(
			[
				'trigger' => $this->trigger,
				'context' => $context,
			]
		);
	}

}

UserUnfollowsMember::get_instance();
