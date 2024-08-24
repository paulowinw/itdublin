<?php
/**
 * UserCompletesLDLesson.
 * php version 5.6
 *
 * @category UserCompletesLDLesson
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

if ( ! class_exists( 'UserCompletesLDLesson' ) ) :


	/**
	 * UserCompletesLDLesson
	 *
	 * @category UserCompletesLDLesson
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 */
	class UserCompletesLDLesson {


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
		public $trigger = 'user_completes_ld_lesson';

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
				'label'         => __( 'Lesson Completed', 'suretriggers' ),
				'action'        => 'user_completes_ld_lesson',
				'common_action' => 'learndash_lesson_completed',
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
				LearnDash::get_course_context( $data['course'] ),
				LearnDash::get_lesson_context( $data['lesson'] )
			);

			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger' => $this->trigger,
					'context' => $context,
				]
			);
		}
	}

	UserCompletesLDLesson::get_instance();

endif;
