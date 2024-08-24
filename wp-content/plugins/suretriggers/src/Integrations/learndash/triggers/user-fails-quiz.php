<?php
/**
 * UserFailsLDQuiz.
 * php version 5.6
 *
 * @category UserFailsLDQuiz
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

if ( ! class_exists( 'UserFailsLDQuiz' ) ) :


	/**
	 * UserFailsLDQuiz
	 *
	 * @category UserFailsLDQuiz
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 */
	class UserFailsLDQuiz {


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
		public $trigger = 'user_fails_ld_quiz';

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
				'label'         => __( 'User Fails Quiz', 'suretriggers' ),
				'action'        => 'user_passes_ld_quiz',
				'common_action' => [ 'learndash_quiz_submitted', 'learndash_essay_quiz_data_updated' ],
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 4,
			];

			return $triggers;

		}

		/**
		 * Trigger listener
		 *
		 * @param array  $data quiz data.
		 * @param object $current_user current user.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $data, $current_user ) {
			
			if ( empty( $data ) ) {
				return;
			}

			$passed = $data['pass'];
			if ( $passed ) {
				return;
			}

			// Check if grading is enabled.
			$has_graded = isset( $data['has_graded'] ) ? absint( $data['has_graded'] ) : 0;
			$has_graded = ! empty( $has_graded );
			$graded     = $has_graded && isset( $data['graded'] ) ? $data['graded'] : false;

			if ( $has_graded ) {
				if ( ! empty( $graded ) ) {
					foreach ( $graded as $grade_item ) {
						// Quiz has not been graded yet.
						if ( isset( $grade_item['status'] ) && 'not_graded' === $grade_item['status'] ) {
							return;
						}
					}
				}
			}

			$output_questions = LearnDash::get_quiz_questions_answers( $data['quiz'] );
			if ( property_exists( $current_user, 'ID' ) ) {
				$current_user = $current_user->ID;
			}
			$context = array_merge(
				WordPress::get_user_context( $current_user ),
				$output_questions,
				$data
			);

			$context['quiz_name']    = get_the_title( $data['quiz'] );
			$context['sfwd_quiz_id'] = $data['quiz'];
			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger' => $this->trigger,
					'context' => $context,
				]
			);
		}
	}

	UserFailsLDQuiz::get_instance();

endif;
