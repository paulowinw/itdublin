<?php
/**
 * WplmsCourseCompleted.
 * php version 5.6
 *
 * @category WplmsCourseCompleted
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\WPLMS\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Integrations\WPLMS\WPLMS;
use SureTriggers\Traits\SingletonLoader;

/**
 * WplmsCourseCompleted
 *
 * @category WplmsCourseCompleted
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class WplmsCourseCompleted {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'WPLMS';

	/**
	 * Trigger name.
	 *
	 * @var string
	 */
	public $trigger = 'wplms_submit_course';

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
			'label'         => __( 'User complete course', 'suretriggers' ),
			'action'        => $this->trigger,
			'common_action' => 'wplms_submit_course',
			'function'      => [ $this, 'trigger_listener' ],
			'priority'      => 10,
			'accepted_args' => 2,
		];

		return $triggers;
	}

	/**
	 * Trigger listener
	 *
	 * @param int $course_id Course id.
	 * @param int $user_id user id.
	 *
	 * @return void
	 */
	public function trigger_listener( $course_id, $user_id ) {
		if ( ! $user_id ) {
			$user_id = ap_get_current_user_id();
		}
		if ( empty( $user_id ) ) {
			return;
		}

		$context = array_merge(
			WordPress::get_user_context( $user_id ),
			WPLMS::get_wplms_course_context( $course_id )
		);

		AutomationController::sure_trigger_handle_trigger(
			[
				'trigger' => $this->trigger,
				'context' => $context,
			]
		);
	}
}

WplmsCourseCompleted::get_instance();
