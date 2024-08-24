<?php
/**
 * UserSubmitsAssignment.
 * php version 5.6
 *
 * @category UserSubmitsAssignment
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

if ( ! class_exists( 'UserSubmitsAssignment' ) ) :


	/**
	 * UserSubmitsAssignment
	 *
	 * @category UserSubmitsAssignment
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 */
	class UserSubmitsAssignment {


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
		public $trigger = 'user_submits_ld_assignment';

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
				'label'         => __( 'User Removed from Group', 'suretriggers' ),
				'action'        => 'user_submits_ld_assignment',
				'common_action' => 'learndash_assignment_uploaded',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 2,
			];

			return $triggers;

		}

		/**
		 * Trigger listener
		 *
		 * @param int   $assignment_post_id  Assignment Post ID.
		 * @param array $assignment_meta Assignment Meta.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $assignment_post_id, $assignment_meta ) {
			if ( empty( $assignment_meta ) ) {
				return;
			}

			$context                         = WordPress::get_user_context( $assignment_meta['user_id'] );
			$context['lesson_id']            = $assignment_meta['lesson_id'];
			$context['sfwd-courses']         = $assignment_meta['course_id'];
			$context['assignment_id']        = $assignment_post_id;
			$context['assignment_url']       = $assignment_meta['file_link'];
			$context['sfwd_lesson_topic_id'] = $assignment_meta['lesson_id'];
			$context['assignment_id']        = $assignment_post_id;
			$context['assignment_title']     = get_the_title( $assignment_post_id );
			$context['points']               = get_post_meta( $assignment_post_id, 'points', true );

			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger' => $this->trigger,
					'context' => $context,
				]
			);
		}
	}

	UserSubmitsAssignment::get_instance();

endif;
