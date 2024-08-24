<?php
/**
 * UserSubmitsMetForm.
 * php version 5.6
 *
 * @category UserSubmitsMetForm
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\MetForm\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'UserSubmitsMetForm' ) ) :

	/**
	 * UserSubmitsMetForm
	 *
	 * @category UserSubmitsMetForm
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class UserSubmitsMetForm {

		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'MetForm';

		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'user_submits_metform';

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
				'action'        => 'user_submits_metform',
				'common_action' => 'metform_after_store_form_data',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 4,
			];

			return $triggers;
		}

		/**
		 * Trigger listener.
		 *
		 * @param int   $form_id form_id.
		 * @param array $form_data form_data.
		 * @param array $form_settings form_settings.
		 * @param array $attributes attributes.
		 *
		 * @return void
		 */
		public function trigger_listener( $form_id, $form_data, $form_settings, $attributes ) {
			if ( empty( $form_id ) ) {
				return;
			}

			$context            = $form_data;
			$context['form_id'] = (int) $form_id;

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
	UserSubmitsMetForm::get_instance();

endif;
