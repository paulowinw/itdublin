<?php
/**
 * LessonCompleted.
 * php version 5.6
 *
 * @category LessonCompleted
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\MemberPressCourse\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\MemberPressCourse\MemberPressCourse;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

/**
 * LessonCompleted
 *
 * @category LessonCompleted
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class LessonCompleted {


	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'MemberPressCourse';

	/**
	 * Trigger name.
	 *
	 * @var string
	 */
	public $trigger = 'mpc_lesson_completed';

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
			'label'         => __( 'Lesson Completed', 'suretriggers' ),
			'action'        => $this->trigger,
			'common_action' => 'mpcs_completed_lesson',
			'function'      => [ $this, 'trigger_listener' ],
			'priority'      => 10,
			'accepted_args' => 1,
		];

		return $triggers;
	}

	/**
	 * Trigger listener
	 *
	 * @param object $data data.
	 *
	 * @return void
	 */
	public function trigger_listener( $data ) {
		if ( ! isset( $data->course_id ) ) {
			return;
		} else {
			$course_id = $data->course_id;
		}

		if ( ! isset( $data->user_id ) ) {
			return;
		} else {
			$user_id = $data->user_id;
		}

		if ( ! isset( $data->lesson_id ) ) {
			return;
		} else {
			$lesson_id = $data->lesson_id;
		}

		$context = array_merge(
			WordPress::get_user_context( $user_id ),
			[
				'lesson_id'                 => $lesson_id,
				'lesson_title'              => get_the_title( $lesson_id ),
				'lesson_url'                => get_permalink( $lesson_id ),
				'lesson_featured_image_id'  => get_post_meta( $lesson_id, '_thumbnail_id', true ),
				'lesson_featured_image_url' => get_the_post_thumbnail_url( $lesson_id ),
			],
			[
				'course_id'                 => $course_id,
				'course_title'              => get_the_title( $course_id ),
				'course_url'                => get_permalink( $course_id ),
				'course_featured_image_id'  => get_post_meta( $course_id, '_thumbnail_id', true ),
				'course_featured_image_url' => get_the_post_thumbnail_url( $course_id ),
			]
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

LessonCompleted::get_instance();
