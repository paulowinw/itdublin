<?php
/**
 * UserSubmitsDiviForm.
 * php version 5.6
 *
 * @category UserSubmitsDiviForm
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\DiviBuilder\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'UserSubmitsDiviForm' ) ) :

	/**
	 * UserSubmitsDiviForm
	 *
	 * @category UserSubmitsDiviForm
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class UserSubmitsDiviForm {

		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'DiviBuilder';

		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'user_submits_diviform';

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
				'action'        => 'user_submits_diviform',
				'common_action' => 'et_pb_contact_form_submit',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 3,
			];
			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param array $fields_values Processed fields values.
		 * @param bool  $et_contact_error Whether there is an error on the form entry submit process or not.
		 * @param array $contact_form_info Additional contact form info.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $fields_values, $et_contact_error, $contact_form_info ) {
			if ( true === $et_contact_error ) {
				return;
			}
			if ( ! isset( $contact_form_info['contact_form_unique_id'] ) ) {
				return;
			}
			$unique_id = $contact_form_info['contact_form_unique_id'];
			$post_id   = $contact_form_info['post_id'];
			$form_id   = "$post_id-$unique_id";
			$user_id   = ap_get_current_user_id();

			foreach ( $fields_values as $key => $value ) {
				$context[ $key ] = stripslashes( $value['value'] );
			}
			$context['divi_form'] = $form_id;
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
	UserSubmitsDiviForm::get_instance();

endif;
