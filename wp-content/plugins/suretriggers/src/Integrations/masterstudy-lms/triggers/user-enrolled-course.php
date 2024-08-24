<?php
/**
 * UserEnrolledCourse.
 * php version 5.6
 *
 * @category UserEnrolledCourse
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

if ( ! class_exists( 'UserEnrolledCourse' ) ) :

	/**
	 * UserEnrolledCourse
	 *
	 * @category UserEnrolledCourse
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class UserEnrolledCourse {


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
		public $trigger = 'stm_lms_user_enroll_course';

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
				'label'         => __( 'User Enrolled InTo Course', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'add_user_course',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 2,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param int $user_id   User add into the course.
		 * @param int $course_id Attempted Course ID.
		 * @return void
		 */
		public function trigger_listener( $user_id, $course_id ) {

			if ( empty( $user_id ) ) {
				return;
			}

			if ( empty( $course_id ) ) {
				return;
			}

			$course         = get_the_title( $course_id );
			$course_link    = get_the_permalink( $course_id );
			$featured_image = get_the_post_thumbnail_url( $course_id );
			$date_joined    = date_i18n( 'Y-m-d H:i:s' );

			$data = [
				'course'                => $course_id,
				'course_title'          => $course,
				'course_link'           => $course_link,
				'course_featured_image' => $featured_image,
				'date_joined'           => $date_joined,
			];

			$context = array_merge( $data, WordPress::get_user_context( $user_id ) );
			
			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger' => $this->trigger,
					'user_id' => $user_id,
					'context' => $context,
				]
			);
		}
	}

	/**
	 * Ignore false positive
	 *
	 * @psalm-suppress UndefinedMethod
	 */
	UserEnrolledCourse::get_instance();

endif;
