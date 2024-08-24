<?php
/**
 * UserEnrolledLDCourse.
 * php version 5.6
 *
 * @category UserEnrolledLDCourse
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\LearnDash\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'UserEnrolledLDCourse' ) ) :


	/**
	 * UserEnrolledLDCourse
	 *
	 * @category UserEnrolledLDCourse
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 */
	class UserEnrolledLDCourse {


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
		public $trigger = 'user_enrolled_ld_course';

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
				'label'         => __( 'User Added in Group', 'suretriggers' ),
				'action'        => 'user_enrolled_ld_course',
				'common_action' => 'learndash_update_course_access',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 4,
			];

			return $triggers;

		}

		/**
		 * Trigger listener
		 *
		 * @param int         $user_id            User ID.
		 * @param int         $course_id          Course ID.
		 * @param string|null $course_access_list A comma-separated list of user IDs used for the course_access_list field.
		 * Note: Used if `learndash_use_legacy_course_access_list()` returns true. Otherwise null is sent.
		 * @param boolean     $remove             Whether to remove course access from the user.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $user_id, $course_id, $course_access_list, $remove ) {
			if ( empty( $user_id ) ) {
				return;
			}

			if ( ! function_exists( 'ld_course_access_expires_on' ) ) {
				return;
			}

			$context                              = WordPress::get_user_context( $user_id );
			$context['sfwd_course_id']            = $course_id;
			$context['course_title']              = get_the_title( $course_id );
			$context['course_url']                = get_permalink( $course_id );
			$context['course_featured_image_id']  = get_post_meta( $course_id, '_thumbnail_id', true );
			$context['course_featured_image_url'] = get_the_post_thumbnail_url( $course_id );
			$context['course_access_expiry_date'] = wp_date( get_option( 'date_format' ), ld_course_access_expires_on( $course_id, $user_id ) );

			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger' => $this->trigger,
					'context' => $context,
				]
			);
		}
	}

	UserEnrolledLDCourse::get_instance();

endif;
