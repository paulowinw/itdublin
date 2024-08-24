<?php
/**
 * WriteActivityPost.
 * php version 5.6
 *
 * @category WriteActivityPost
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\PeepSo\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\PeepSo\PeepSo;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

/**
 * WriteActivityPost
 *
 * @category WriteActivityPost
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class WriteActivityPost {


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
	public $trigger = 'peepso_activity_after_add_post';

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
			'label'         => __( 'New Activity Post', 'suretriggers' ),
			'action'        => $this->trigger,
			'common_action' => 'peepso_activity_after_add_post',
			'function'      => [ $this, 'trigger_listener' ],
			'priority'      => 10,
			'accepted_args' => 2,
		];

		return $triggers;
	}

	/**
	 * Trigger listener
	 *
	 * @param int $post_id user id.
	 * @param int $activity_id activity id.
	 *
	 * @return void
	 */
	public function trigger_listener( $post_id, $activity_id ) {

		$user_id = absint( get_post_field( 'post_author', $post_id ) );

		if ( empty( $user_id ) ) {
			return;
		}

		$context = array_merge(
			WordPress::get_user_context( $user_id ),
			PeepSo::get_pp_activity_context( $post_id, $activity_id )
		);

		AutomationController::sure_trigger_handle_trigger(
			[
				'trigger' => $this->trigger,
				'user_id' => $user_id,
				'context' => $context,
			]
		);
	}

}

WriteActivityPost::get_instance();
