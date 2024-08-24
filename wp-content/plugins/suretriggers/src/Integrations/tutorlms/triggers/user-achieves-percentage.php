<?php
/**
 * UserAchievesPercentage.
 * php version 5.6
 *
 * @category UserAchievesPercentage
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\TutorLMS\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

/**
 * UserAchievesPercentage
 *
 * @category UserAchievesPercentage
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class UserAchievesPercentage {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'TutorLMS';

	/**
	 * Trigger name.
	 *
	 * @var string
	 */
	public $trigger = 'tlms_quiz_percentage';

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
			'label'         => __( 'User passes Quiz', 'suretriggers' ),
			'action'        => $this->trigger,
			'common_action' => 'tutor_quiz/attempt_ended',
			'function'      => [ $this, 'trigger_listener' ],
			'priority'      => 10,
			'accepted_args' => 1,
		];

		return $triggers;
	}

	/**
	 * Trigger listener.
	 *
	 * @param int $attempt_id Attempt ID.
	 *
	 * @return void
	 */
	public function trigger_listener( $attempt_id ) {

		if ( ! function_exists( 'tutor_utils' ) ) {
			return;
		}
		
		$attempt = tutor_utils()->get_attempt( $attempt_id );
		if ( 'tutor_quiz' !== get_post_type( $attempt->quiz_id ) ) {
			return;
		}
		
		$score = (int) $attempt->earned_marks;

		$context               = WordPress::get_user_context( $attempt->user_id );
		$context['quiz_id']    = $attempt->quiz_id;
		$context['attempt_id'] = $attempt_id;
		$context['attempt']    = $attempt;
		$context['percentage'] = $score;
		AutomationController::sure_trigger_handle_trigger(
			[
				'trigger' => $this->trigger,
				'context' => $context,
			]
		);
	}
}

UserAchievesPercentage::get_instance();
