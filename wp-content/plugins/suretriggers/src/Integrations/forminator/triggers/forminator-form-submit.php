<?php
/**
 * ForminatorFormSubmit.
 * php version 5.6
 *
 * @category ForminatorFormSubmit
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\Forminator\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;

if ( ! class_exists( 'ForminatorFormSubmit' ) ) :

	/**
	 * ForminatorFormSubmit
	 *
	 * @category ForminatorFormSubmit
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class ForminatorFormSubmit {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'Forminator';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'forminatorform_submitted';

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
				'label'         => __( 'A form is submitted', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'forminator_custom_form_submit_before_set_fields',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 100,
				'accepted_args' => 3,
			];
			return $triggers;

		}

		/**
		 * Trigger listener
		 *
		 * @param object $entry Entry Response.
		 * @param int    $form_id Form ID.
		 * @param array  $field_data_array data array.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $entry, $form_id, $field_data_array ) {
			$form_fields = [];
			$field_name  = 'name';
			$field_value = 'value';
			foreach ( $field_data_array as $field ) {

				if ( ! isset( $field[ $field_name ] ) && ! isset( $field[ $field_value ] ) ) {
					continue;
				}
		
				$name  = $field[ $field_name ];
				$value = $field[ $field_value ];
		
				$form_fields[ $name ] = $value;
			}

			$user_id = ap_get_current_user_id();
			if ( is_int( $user_id ) ) {
				$context['user'] = WordPress::get_user_context( $user_id );
			}

			$context['form']            = $form_fields;
			$context['forminator_form'] = $form_id;

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
	ForminatorFormSubmit::get_instance();

endif;
