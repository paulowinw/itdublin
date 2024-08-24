<?php
/**
 * PfdFeedbackReceived.
 * php version 5.6
 *
 * @category PfdFeedbackReceived
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\PowerfulDocs\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;

if ( ! class_exists( 'PfdFeedbackReceived' ) ) :

	/**
	 * PfdFeedbackReceived
	 *
	 * @category PfdFeedbackReceived
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class PfdFeedbackReceived {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'PowerfulDocs';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'pfd_feedback_received';

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
				'label'         => __( 'Feedback Received', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'pfd_feedback_form_submitted',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 1,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param array $data Data.
		 * @return void
		 */
		public function trigger_listener( $data ) {
			
			if ( empty( $data ) ) {
				return;
			}
			
			$context = $data;
			$user_id = ap_get_current_user_id();
			if ( '' != $user_id ) {
				$context = array_merge( WordPress::get_user_context( intval( '"' . $user_id . '"' ) ), $context );
			}
			$context['doc_name']         = get_the_title( $data['doc_id'] );
			$context['doc_link']         = get_the_permalink( $data['doc_id'] );
			$author_id                   = get_post_field( 'post_author', $data['doc_id'] );
			$email                       = get_the_author_meta( 'user_email', intval( '"' . $author_id . '"' ) );
			$context['doc_author_email'] = $email;
			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger'    => $this->trigger,
					'wp_user_id' => $user_id,
					'context'    => $context,
				]
			);
		}
	}

	/**
	 * Ignore false positive
	 *
	 * @psalm-suppress UndefinedMethod
	 */
	PfdFeedbackReceived::get_instance();

endif;
