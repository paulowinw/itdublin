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

namespace SureTriggers\Integrations\MemberPressCourse\Actions;

use Exception;
use memberpress\courses\lib as lib;
use SureTriggers\Integrations\MemberPressCourse\MemberPressCourse;
use memberpress\courses as base;
use memberpress\courses\models as models;
use SureTriggers\Integrations\AutomateAction;
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
class CompleteCourse extends AutomateAction {


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
	public $action = 'mpc_complete_course';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Complete Course', 'suretriggers' ),
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

		$sections  = [];
		$lessons   = [];
		$course_id = $selected_options['mpcs-course'];
		$sections  = $this->find_all_by_course( $course_id );
	
		if ( is_array( $sections ) && count( $sections ) > 0 ) {
			foreach ( $sections as $section ) {
				$lessons = MemberpressCourse::find_all_by_section( $section );
				if ( is_array( $lessons ) && count( $lessons ) > 0 ) {
					foreach ( $lessons as $lesson ) {
						$this->mark_lesson_completed( $user_id, $course_id, $lesson, $section );
					}
				}
			}
		}
		
		return [
			'user_email'                => $selected_options['wp_user_email'],
			'course_id'                 => $course_id,
			'course_title'              => get_the_title( $course_id ),
			'course_url'                => get_permalink( $course_id ),
			'course_featured_image_id'  => get_post_meta( $course_id, '_thumbnail_id', true ),
			'course_featured_image_url' => get_the_post_thumbnail_url( $course_id ),
		];
	}

	/**
	 * Mark lesson completed.
	 * 
	 * @param int $user_id user id.
	 * @param int $course_id course id.
	 * @param int $lesson_id lesson id.
	 * @param int $section  section.
	 * @return void
	 */
	public function mark_lesson_completed( $user_id, $course_id, $lesson_id, $section ) {
		if ( ! class_exists( '\memberpress\courses\models\UserProgress' ) ) {
			return;
		}
		if ( empty( $section ) && empty( $course_id ) ) {
			return;
		}

		if ( models\UserProgress::has_completed_course( $user_id, $course_id ) ) {
			return;
		}

		$has_started_course  = models\UserProgress::has_started_course( $user_id, $course_id );
		$has_started_section = models\UserProgress::has_started_section( $user_id, $section );

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

		if ( models\UserProgress::has_completed_course( $user_id, $course_id ) ) {
			do_action( 'mpcs_completed_course', $user_progress );
		}

		if ( models\UserProgress::has_completed_section( $user_id, $section ) ) {
			do_action( 'mpcs_completed_section', $user_progress );
		}
	}

	/**
	 * Find all sections
	 * 
	 * @param int $course_id course id.
	 * @return array
	 */
	public function find_all_by_course( $course_id ) {
		global $wpdb;
		$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  {$wpdb->prefix}mpcs_sections WHERE course_id =%s", $course_id ) );

		$sections = [];
		foreach ( $result as $rec ) {
			$sections[] = $rec->id;
		}

		return $sections;
	}

	/**
	 * Find lessons. 
	 * 
	 * @param int $section_id section id.
	 * @return array|void
	 */
	public function find_all_by_section( $section_id ) {
		if ( ! class_exists( '\memberpress\courses\models\Lesson' ) ) {
			return;
		}
		global $wpdb;
		$post_types_string = models\Lesson::lesson_cpts();
		$post_types_string = implode( "','", $post_types_string );

		$query = $wpdb->prepare(
			"SELECT ID, post_type FROM {$wpdb->posts} AS p
	        JOIN {$wpdb->postmeta} AS pm
	          ON p.ID = pm.post_id
	         AND pm.meta_key = %s
	         AND pm.meta_value = %s
	        JOIN {$wpdb->postmeta} AS pm_order
	          ON p.ID = pm_order.post_id
	         AND pm_order.meta_key = %s
	       WHERE p.post_type in ( %s ) AND p.post_status <> 'trash'
	       ORDER BY pm_order.meta_value * 1",
			models\Lesson::$section_id_str,
			$section_id,
			models\Lesson::$lesson_order_str,
			stripcslashes( $post_types_string )
		);

		$db_lessons = $wpdb->get_results( stripcslashes( $query ) ); //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$lessons    = [];

		foreach ( $db_lessons as $lesson ) {
			if ( class_exists( '\memberpress\courses\models\Quiz' ) && models\Quiz::$cpt === $lesson->post_type ) {
				$lessons[] = $lesson->ID;
			} else {
				$lessons[] = $lesson->ID;
			}
		}

		return $lessons;
	}

}

CompleteCourse::get_instance();
