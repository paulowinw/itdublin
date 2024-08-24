<?php
/**
 * SlUserFailsQuiz.
 * php version 5.6
 *
 * @category SlUserFailsQuiz
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\SenseiLMS\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

/**
 * SlUserFailsQuiz
 *
 * @category SlUserFailsQuiz
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class SlUserFailsQuiz {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'SenseiLMS';

	/**
	 * Trigger name.
	 *
	 * @var string
	 */
	public $trigger = 'sl_user_fails_quiz';

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
	 *
	 * @return array
	 */
	public function register( $triggers ) {
		$triggers[ $this->integration ][ $this->trigger ] = [
			'label'         => __( 'User Fails Quiz', 'suretriggers' ),
			'action'        => $this->trigger,
			'common_action' => 'sensei_user_quiz_grade',
			'function'      => [ $this, 'trigger_listener' ],
			'priority'      => 10,
			'accepted_args' => 5,
		];

		return $triggers;
	}

	/**
	 * Trigger listener.
	 *
	 * @param int    $user_id   The user ID.
	 * @param int    $quiz_id The quiz ID.
	 * @param int    $grade The grade.
	 * @param int    $quiz_passmark The quiz passmark.
	 * @param string $quiz_grade_type The quiz grade type.
	 *
	 * @return void
	 */
	public function trigger_listener( $user_id, $quiz_id, $grade, $quiz_passmark, $quiz_grade_type ) {
		// Return if passed.
		if ( $grade > $quiz_passmark ) {
			return;
		}
		if ( ! function_exists( 'Sensei' ) ) {
			return;
		}
		global $wpdb;
		$quiz = get_post( $quiz_id );

		$context = WordPress::get_user_context( $user_id );
		if ( $quiz instanceof \WP_Post ) {
			$context['quiz_title'] = $quiz->post_title;
		}
		$submission                          = \Sensei()->quiz_submission_repository->get( $quiz_id, $user_id );
		$comment_type                        = 'sensei_lesson_status';
		$sql                                 = "SELECT * FROM {$wpdb->prefix}comments WHERE comment_type = %s AND comment_ID = %d";
		$results      = $wpdb->get_results( $wpdb->prepare( $sql, $comment_type, $submission->get_id() ), ARRAY_A );// @phpcs:ignore
		$context['quiz_status']              = 'failed';
		$context['quiz_data']['id']          = $submission->get_id();
		$context['sensei_quiz']              = $results[0]['comment_post_ID'];
		$context['quiz_data']['final_grade'] = $submission->get_final_grade();
		$context['quiz_data']['created_at']  = $submission->get_created_at();
		AutomationController::sure_trigger_handle_trigger(
			[
				'trigger' => $this->trigger,
				'context' => $context,
			]
		);
	}
}

SlUserFailsQuiz::get_instance();
