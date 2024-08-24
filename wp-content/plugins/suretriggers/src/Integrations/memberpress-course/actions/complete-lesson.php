<?php
/**
 * CompleteCourse.
 * php version 5.6
 *
 * @category CompleteLesson
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\MemberPressCourse\Actions;

use Exception;
use memberpress\courses\lib as lib;
use memberpress\courses as base;
use memberpress\courses\models as models;
use SureTriggers\Integrations\AutomateAction;
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
class CompleteLesson extends AutomateAction {


	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'MemberPressCourse';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'mpc_complete_lesson';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Complete Lesson', 'suretriggers' ),
			'action'   => $this->action,
			'function' => [ $this, 'action_listener' ],
		];
		return $actions;
	}

	/**
	 * Action listener.
	 *
	 * @param int   $user_id user_id.
	 * @param int   $automation_id automation_id.
	 * @param array $fields fields.
	 * @param array $selected_options selectedOptions.
	 * @throws Exception Throws exception.
	 *
	 * @return array
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		if ( ! $user_id ) {
			throw new Exception( 'User not found with this email address.' );
		}

		$course_id = $selected_options['course'];
		$lesson_id = $selected_options['lesson'];

		$this->mark_lesson_completed( $user_id, $course_id, $lesson_id );
		return [
			'user_email'                => $selected_options['wp_user_email'],
			'course_id'                 => $course_id,
			'course_title'              => get_the_title( $course_id ),
			'course_url'                => get_permalink( $course_id ),
			'course_featured_image_id'  => get_post_meta( $course_id, '_thumbnail_id', true ),
			'course_featured_image_url' => get_the_post_thumbnail_url( $course_id ),
			'lesson_id'                 => $lesson_id,
			'lesson_title'              => get_the_title( $lesson_id ),
			'lesson_url'                => get_permalink( $lesson_id ),
			'lesson_featured_image_id'  => get_post_meta( $lesson_id, '_thumbnail_id', true ),
			'lesson_featured_image_url' => get_the_post_thumbnail_url( $lesson_id ),
		];

	}

	/**
	 * Mark lesson complete.
	 * 
	 * @param int $user_id user's id.
	 * @param int $course_id course id.
	 * @param int $lesson_id lesson id.
	 * @return void
	 */
	public function mark_lesson_completed( $user_id, $course_id, $lesson_id ) {
		if ( ! class_exists( '\memberpress\courses\models\UserProgress' ) ) {
			return;
		}
		if ( empty( $lesson_id ) && empty( $course_id ) ) {
			return;
		}

		if ( models\UserProgress::has_completed_course( $user_id, $course_id ) ) {
			return;
		}

		$user_progress            = new models\UserProgress();
		$user_progress->lesson_id = $lesson_id;
		$user_progress->course_id = $course_id;
		$user_progress->user_id   = $user_id;
		if ( class_exists( '\memberpress\courses\lib\Utils' ) ) {
			$user_progress->created_at   = lib\Utils::ts_to_mysql_date( time() );
			$user_progress->completed_at = lib\Utils::ts_to_mysql_date( time() );
		}
		$user_progress->store();

		do_action( 'mpcs_completed_lesson', $user_progress );
	}

}

CompleteLesson::get_instance();
