<?php
/**
 * UserSubmitsBeaverBuilderForm.
 * php version 5.6
 *
 * @category UserSubmitsBeaverBuilderForm
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\BeaverBuilder\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'UserSubmitsBeaverBuilderForm' ) ) :

	/**
	 * UserSubmitsBeaverBuilderForm
	 *
	 * @category UserSubmitsBeaverBuilderForm
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 */
	class UserSubmitsBeaverBuilderForm {

		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'BeaverBuilder';

		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'user_submits_beaver_builder_form';

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
				'label'         => __( 'User Submits Contact/Subscribe Form', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'suretriggers_bb_after_form_submit',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 1,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param array $context context Context Data.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $context ) {
			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger'    => $this->trigger,
					'wp_user_id' => ap_get_current_user_id(),
					'context'    => $context,
				]
			);
		}
	}

	UserSubmitsBeaverBuilderForm::get_instance();

endif;
