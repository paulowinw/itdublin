<?php
/**
 * QuizPassed.
 * php version 5.6
 *
 * @category QuizPassed
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

if ( ! class_exists( 'QuizPassed' ) ) :

	/**
	 * QuizPassed
	 *
	 * @category QuizPassed
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class QuizPassed {


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
		public $trigger = 'stm_quiz_passed';

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
				'label'         => __( 'Quiz Passed', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'stm_lms_quiz_passed',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 3,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param int $user_id  User attempting the quiz.
		 * @param int $quiz_id  Attempted Quiz ID.
		 * @param int $progress Quiz result.
		 * @return void
		 */
		public function trigger_listener( $user_id, $quiz_id, $progress ) {

			if ( empty( $quiz_id ) ) {
				return;
			}

			if ( empty( $user_id ) ) {
				return;
			}

			$quiz_title     = get_the_title( $quiz_id );
			$quiz_link      = get_the_permalink( $quiz_id );
			$date_completed = date_i18n( 'Y-m-d H:i:s' );

			$data = [
				'quiz'           => $quiz_id,
				'quiz_title'     => $quiz_title,
				'quiz_link'      => $quiz_link,
				'quiz_score'     => $progress,
				'result'         => 'passed',
				'date_completed' => $date_completed,
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
	QuizPassed::get_instance();

endif;
