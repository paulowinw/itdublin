<?php
/**
 * CompleteLesson.
 * php version 5.6
 *
 * @category CompleteLesson
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\TutorLMS\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

/**
 * CompleteLesson
 *
 * @category CompleteLesson
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class CompleteLesson {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'TutorLMS';

	/**
	 * Trigger name.
	 *
	 * @var string
	 */
	public $trigger = 'tutor_lesson_completed_after';

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
			'label'         => __( 'User complete Lesson', 'suretriggers' ),
			'action'        => $this->trigger,
			'function'      => [ $this, 'trigger_listener' ],
			'priority'      => 10,
			'accepted_args' => 2,
		];

		return $triggers;
	}

	/**
	 * Trigger listener.
	 *
	 * @param int $lesson_id CourserID.
	 * @param int $user_id User ID.
	 *
	 * @return void
	 */
	public function trigger_listener( $lesson_id, $user_id ) {
		$lesson = get_post( $lesson_id );

		$context                 = WordPress::get_user_context( $user_id );
		$context['lesson_id']    = $lesson_id;
		$context['lesson_title'] = $lesson->post_title;

		AutomationController::sure_trigger_handle_trigger(
			[
				'trigger' => $this->trigger,
				'context' => $context,
			]
		);
	}
}

CompleteLesson::get_instance();
