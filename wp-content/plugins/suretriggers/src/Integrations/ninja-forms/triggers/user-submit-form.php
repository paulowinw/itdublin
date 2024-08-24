<?php
/**
 * UserSubmitsNinjaForms.
 * php version 5.6
 *
 * @category UserSubmitsNinjaForms
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\NinjaForms\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'UserSubmitsNinjaForms' ) ) :

	/**
	 * UserSubmitsNinjaForms
	 *
	 * @category UserSubmitsNinjaForms
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class UserSubmitsNinjaForms {

		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'NinjaForms';

		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'user_submits_ninjaforms';

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
				'label'         => __( 'User Submits Form', 'suretriggers' ),
				'action'        => 'user_submits_ninjaforms',
				'common_action' => 'ninja_forms_after_submission',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 1,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param array $data data.
		 *
		 * @return void
		 */
		public function trigger_listener( $data ) {
			if ( empty( $data ) || ! isset( $data['form_id'] ) || ! isset( $data['fields_by_key'] ) ) {
				return;
			}

			$context            = [];
			$context['form_id'] = $data['form_id'];

			foreach ( $data['fields_by_key'] as $key => $field ) {
				$context[ $key ] = $field['value'];
			}

			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger' => $this->trigger,
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
	UserSubmitsNinjaForms::get_instance();

endif;
