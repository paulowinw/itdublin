<?php
/**
 * UserAssignmentGraded.
 * php version 5.6
 *
 * @category UserAssignmentGraded
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

if ( ! class_exists( 'UserAssignmentGraded' ) ) :


	/**
	 * UserAssignmentGraded
	 *
	 * @category UserAssignmentGraded
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 */
	class UserAssignmentGraded {


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
		public $trigger = 'user_assignment_ld_graded';

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
				'label'         => __( 'User Submits Essay Quiz', 'suretriggers' ),
				'action'        => 'user_assignment_ld_graded',
				'common_action' => 'learndash_assignment_approved',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 1,
			];

			return $triggers;

		}

		/**
		 * Trigger listener
		 *
		 * @param int $assignment_id  Assignment ID.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $assignment_id ) {
			if ( empty( $assignment_id ) ) {
				return;
			}

			$assignments = WordPress::get_post_context( $assignment_id );

			$context                         = WordPress::get_user_context( (int) $assignments['post_author'] );
			$context['sfwd_assignment_id']   = $assignment_id;
			$context['assignment_title']     = get_the_title( $assignment_id );
			$context['assignment_url']       = get_post_meta( $assignment_id, 'file_link', true );
			$context['sfwd_lesson_topic_id'] = get_post_meta( $assignment_id, 'lesson_id', true );
			$context['sfwd-courses']         = get_post_meta( $assignment_id, 'course_id', true );
			$context['points']               = get_post_meta( $assignment_id, 'points', true );

			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger' => $this->trigger,
					'context' => $context,
				]
			);
		}
	}

	UserAssignmentGraded::get_instance();

endif;
