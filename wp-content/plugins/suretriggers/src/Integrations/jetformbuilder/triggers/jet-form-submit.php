<?php
/**
 * JetFormSubmit.
 * php version 5.6
 *
 * @category JetFormSubmit
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\JetFormBuilder\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;

if ( ! class_exists( 'JetFormSubmit' ) ) :

	/**
	 * JetFormSubmit
	 *
	 * @category JetFormSubmit
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class JetFormSubmit {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'JetFormBuilder';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'jetform_submitted';

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
				'label'         => __( 'Form Submitted', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'jet-form-builder/form-handler/after-send',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 2,
			];
			return $triggers;

		}

		/**
		 * Trigger listener
		 *
		 * @param object $form_handler Response Data.
		 * @param bool   $is_success Post Data.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $form_handler, $is_success ) {
			if ( empty( $form_handler->action_handler->form_id ) ) {
				return;
			}

			if ( property_exists( $form_handler, 'response_args' ) && 'success' !== $form_handler->response_args['status'] ) {
				return;
			}

			$form_fields = [];
			if ( property_exists( $form_handler->action_handler, 'request_data' ) ) {
				$all_fields = $form_handler->action_handler->request_data;
				foreach ( $all_fields as $field_key => $field_value ) {
					if ( empty( $field_value ) ) {
						continue;
					}
					if ( substr( $field_key, 0, 2 ) === '__' ) {
						continue;
					}
					if ( is_string( $field_value ) && json_decode( $field_value ) !== null ) {
						$form_fields[ $field_key ] = json_decode( $field_value, true );
					} else {
						$form_fields[ $field_key ] = $field_value;
					}
				}
			}

			$user_id = ap_get_current_user_id();
			if ( is_int( $user_id ) ) {
				$context['user'] = WordPress::get_user_context( $user_id );
			}
			
			$context['jet_form']  = $form_handler->action_handler->form_id;
			$context['form_data'] = $form_fields;

			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger'    => $this->trigger,
					'wp_user_id' => ap_get_current_user_id(),
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
	JetFormSubmit::get_instance();

endif;
