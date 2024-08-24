<?php
/**
 * CourseCompleted.
 * php version 5.6
 *
 * @category CourseCompleted
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\MasterStudyLms\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;

if ( ! class_exists( 'CourseCompleted' ) ) :

	/**
	 * CourseCompleted
	 *
	 * @category CourseCompleted
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class CourseCompleted {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'MasterStudyLms';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'stm_lms_course_completed';

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
				'label'         => __( 'Course Completed', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'stm_lms_progress_updated',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 3,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param int $course_id Attempted Course ID.
		 * @param int $user_id   User attempting the quiz.
		 * @param int $progress  Course progress.
		 * @return void
		 */
		public function trigger_listener( $course_id, $user_id, $progress ) {

			// If progress not proviced, abort!
			if ( empty( $progress ) ) {
				return;
			}

			if ( empty( $user_id ) ) {
				return;
			}

			// Get the user's progress for the course selected.
			if ( class_exists( '\STM_LMS_Lesson' ) ) {
				$total_progress = \STM_LMS_Lesson::get_total_progress( $user_id, $course_id );
			}
			
			if ( ! empty( $total_progress ) && 100 === absint( $progress ) ) {
				$course         = get_the_title( $course_id );
				$course_link    = get_the_permalink( $course_id );
				$featured_image = get_the_post_thumbnail_url( $course_id );
				$date_completed = date_i18n( 'Y-m-d H:i:s' );

				$data = [
					'course'                => $course_id,
					'course_title'          => $course,
					'course_link'           => $course_link,
					'course_featured_image' => $featured_image,
					'course_progress'       => $progress,
					'date_completed'        => $date_completed,
				];

				$context = array_merge( $data, WordPress::get_user_context( $user_id ) );
			
				AutomationController::sure_trigger_handle_trigger(
					[
						'trigger'    => $this->trigger,
						'wp_user_id' => $user_id,
						'context'    => $context,
					]
				);
			}
		}
	}

	/**
	 * Ignore false positive
	 *
	 * @psalm-suppress UndefinedMethod
	 */
	CourseCompleted::get_instance();

endif;
