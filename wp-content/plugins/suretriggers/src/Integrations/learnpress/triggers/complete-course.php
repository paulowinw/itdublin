<?php
/**
 * CompleteCourse.
 * php version 5.6
 *
 * @category CompleteCourse
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\LearnPress\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\LearnPress\LearnPress;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

/**
 * CompleteCourse
 *
 * @category CompleteCourse
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class CompleteCourse {


	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'LearnPress';

	/**
	 * Trigger name.
	 *
	 * @var string
	 */
	public $trigger = 'learnpress_course_completed';

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
			'label'         => __( 'User completes course', 'suretriggers' ),
			'action'        => $this->trigger,
			'common_action' => 'learn-press/user-course-finished',
			'function'      => [ $this, 'trigger_listener' ],
			'priority'      => 10,
			'accepted_args' => 3,
		];

		return $triggers;
	}

	/**
	 * Trigger listener
	 *
	 * @param int   $course_id Course id .
	 * @param int   $user_id user id.
	 * @param mixed $result result.
	 *
	 * @return void
	 */
	public function trigger_listener( $course_id, $user_id, $result ) {
		if ( empty( $user_id ) ) {
			return;
		}
		$context = array_merge(
			WordPress::get_user_context( $user_id ),
			LearnPress::get_lpc_course_context( $course_id )
		);
		AutomationController::sure_trigger_handle_trigger(
			[
				'trigger'    => $this->trigger,
				'wp_user_id' => $user_id,
				'context'    => $context,
			]
		);
	}

}

CompleteCourse::get_instance();
