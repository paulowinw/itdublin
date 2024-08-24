<?php
/**
 * HappyFormsFormSubmit.
 * php version 5.6
 *
 * @category HappyFormsFormSubmit
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\HappyForms\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;

if ( ! class_exists( 'HappyFormsFormSubmit' ) ) :

	/**
	 * HappyFormsFormSubmit
	 *
	 * @category HappyFormsFormSubmit
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class HappyFormsFormSubmit {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'HappyForms';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'happyforms_form_submitted';

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
				'common_action' => 'happyforms_submission_success',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 20,
				'accepted_args' => 3,
			];
			return $triggers;

		}

		/**
		 * Trigger listener
		 *
		 * @param array $submission Response Data.
		 * @param array $form Post Data.
		 * @param bool  $misc Post Data.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $submission, $form, $misc ) {
			
			if ( ! function_exists( 'happyforms_get_form_controller' ) ) {
				return;
			}

			$fields = [];
			
			$form_id         = absint( $form['ID'] );
			$form_controller = happyforms_get_form_controller();

			$form = $form_controller->get( $form_id );

			if ( is_array( $form ) && ! empty( $form['parts'] ) ) {
				foreach ( $form['parts'] as $field ) {
					$input_id    = $field['id'];
					$input_title = $field['label'];
					$fields[]    = [
						'value' => $input_id,
						'text'  => $input_title,
					];
				}
			}

			$result = [];
			foreach ( $fields as $key => $val ) {
				$result[ $val['text'] ] = $submission[ $val['value'] ]; 
			}

			$user_id = ap_get_current_user_id();
			if ( is_int( $user_id ) ) {
				$context['user'] = WordPress::get_user_context( $user_id );
			}

			$context['data']      = $result;
			$context['happyform'] = $form['ID'];

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
	HappyFormsFormSubmit::get_instance();

endif;
