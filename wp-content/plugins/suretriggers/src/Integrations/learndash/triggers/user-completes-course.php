<?php
/**
 * UserCompletesLDCourse.
 * php version 5.6
 *
 * @category UserCompletesLDCourse
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\LearnDash\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\LearnDash\LearnDash;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'UserCompletesLDCourse' ) ) :


	/**
	 * UserCompletesLDCourse
	 *
	 * @category UserCompletesLDCourse
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 */
	class UserCompletesLDCourse {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'LearnDash';

		/**
		 * Action name.
		 *
		 * @var string
		 */
		public $trigger = 'user_completes_ld_course';

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
				'label'         => __( 'Course Completed', 'suretriggers' ),
				'action'        => 'user_completes_ld_course',
				'common_action' => 'learndash_course_completed',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 1,
			];

			return $triggers;

		}

		/**
		 * Trigger listener
		 *
		 * @param array $data course data.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $data ) {
			if ( empty( $data ) ) {
				return;
			}

			$context = array_merge(
				WordPress::get_user_context( $data['user']->ID ),
				LearnDash::get_course_context( $data['course'] )
			);

			$context['course_status']          = $data['course']->post_status;
			$context['course_completion_date'] = wp_date( get_option( 'date_format' ), get_user_meta( $data['user']->ID, 'course_completed_' . $data['course']->ID, true ) );
			if ( function_exists( 'learndash_get_course_certificate_link' ) ) {
				$context['course_certificate'] = learndash_get_course_certificate_link( $data['course']->ID, $data['user']->ID );
			}

			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger' => $this->trigger,
					'context' => $context,
				]
			);
		}
	}

	UserCompletesLDCourse::get_instance();

endif;
