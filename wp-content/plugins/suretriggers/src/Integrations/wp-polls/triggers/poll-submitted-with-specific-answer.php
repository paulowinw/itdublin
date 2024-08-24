<?php
/**
 * PollSubmittedWithSpecificAnswer.
 * php version 5.6
 *
 * @category PollSubmittedWithSpecificAnswer
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\WpPolls\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\WpPolls\WpPolls;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'PollSubmittedWithSpecificAnswer' ) ) :

	/**
	 * PollSubmittedWithSpecificAnswer
	 *
	 * @category PollSubmittedWithSpecificAnswer
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class PollSubmittedWithSpecificAnswer {

		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'WpPolls';

		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'wp_polls_poll_submitted_with_specific_answer';

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
				'label'         => __( 'Poll Submitted with a Specific Answer', 'suretriggers' ),
				'action'        => 'wp_polls_poll_submitted_with_specific_answer',
				'common_action' => 'wp_polls_vote_poll_success',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 0,
			];

			return $triggers;

		}

		/**
		 * Trigger listener
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function trigger_listener() {
			if ( ! isset( $_POST['poll_id'] ) || ! isset( $_POST[ 'poll_' . $_POST['poll_id'] ] ) ) {
				return;
			}

			$poll_id = (int) sanitize_key( $_POST['poll_id'] );

			if ( ! check_ajax_referer( 'poll_' . $poll_id . '-nonce', 'poll_' . $poll_id . '_nonce', false ) ) {
				return;
			}

			$selected_answers_ids_str = sanitize_text_field( $_POST[ 'poll_' . $_POST['poll_id'] ] );
			$selected_answers_ids     = explode( ',', $selected_answers_ids_str );

			foreach ( $selected_answers_ids as $selected_answer_id ) {
				$context                       = WpPolls::get_poll_context( $selected_answers_ids_str, $poll_id );
				$context['selected_answer_id'] = $selected_answer_id;

				AutomationController::sure_trigger_handle_trigger(
					[
						'trigger' => $this->trigger,
						'context' => $context,
					]
				);
			}
		}
	}

	/**
	 * Ignore false positive
	 *
	 * @psalm-suppress UndefinedMethod
	 */
	PollSubmittedWithSpecificAnswer::get_instance();

endif;
