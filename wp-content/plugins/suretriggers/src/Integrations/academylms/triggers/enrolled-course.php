<?php
/**
 * EnrolledCourse.
 * php version 5.6
 *
 * @category EnrolledCourse
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\AcademyLMS\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;

if ( ! class_exists( 'EnrolledCourse' ) ) :

	/**
	 * EnrolledCourse
	 *
	 * @category EnrolledCourse
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class EnrolledCourse {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'AcademyLMS';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'ac_lms_enrolled_course';

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
				'label'         => __( 'Enrolled Course', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'academy/course/after_enroll',
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
		 * @param int $enroll_id Enrolled ID.
		 * @param int $user_id   User ID.
		 * @return void
		 */
		public function trigger_listener( $course_id, $enroll_id, $user_id ) {

			global $wpdb;
			if ( empty( $user_id ) ) {
				return;
			}

			$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}posts WHERE post_type = %s AND post_parent = %d order by ID DESC LIMIT 1", 'academy_enrolled', $course_id ) );

			$data                       = WordPress::get_post_context( $result[0]->post_parent );
			$context                    = WordPress::get_user_context( $result[0]->post_author );
			$context['course_data']     = $data;
			$context['enrollment_data'] = $result[0];
			$context['course']          = $course_id;
		
			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger'    => $this->trigger,
					'wp_user_id' => $user_id,
					'context'    => $context,
				]
			);
		}
	}

	/**
	 * Ignore false positive
	 *
	 * @psalm-suppress UndefinedMethod
	 */
	EnrolledCourse::get_instance();

endif;
