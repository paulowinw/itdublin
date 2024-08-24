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

namespace SureTriggers\Integrations\LifterLMS\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\LifterLMS\LifterLMS;
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
	public $integration = 'LifterLMS';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $trigger = 'lifterlms_lesson_completed';

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
	 * Register a action.
	 *
	 * @param array $triggers actions.
	 * @return array
	 */
	public function register( $triggers ) {

		$triggers[ $this->integration ][ $this->trigger ] = [
			'label'         => __( 'User Completes a Lesson', 'suretriggers' ),
			'action'        => $this->trigger,
			'common_action' => 'lifterlms_lesson_completed',
			'function'      => [ $this, 'trigger_listener' ],
			'priority'      => 10,
			'accepted_args' => 2,
		];

		return $triggers;
	}


	/**
	 * Trigger listener.
	 *
	 * @param int $user_id id user ID.
	 * @param int $lesson_id Lesson ID.
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function trigger_listener( $user_id, $lesson_id ) {
		if ( ! $user_id ) {
			$user_id = ap_get_current_user_id();
		}
		if ( empty( $user_id ) ) {
			return;
		}

		$context           = array_merge(
			WordPress::get_user_context( $user_id ),
			LifterLMS::get_lms_lesson_context( $lesson_id )
		);
		$context['course'] = get_the_title( get_post_meta( $lesson_id, '_llms_parent_course', true ) );
		if ( '' !== ( get_post_meta( $lesson_id, '_llms_parent_section', true ) ) ) {
			$context['parent_section'] = get_the_title( get_post_meta( $lesson_id, '_llms_parent_section', true ) );
		}

		AutomationController::sure_trigger_handle_trigger(
			[
				'trigger' => $this->trigger,
				'user_id' => $user_id,
				'context' => $context,
			]
		);
	}
}

CompleteLesson::get_instance();
