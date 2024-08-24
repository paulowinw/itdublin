<?php
/**
 * NewUserSubmitsElementorForm.
 * php version 5.6
 *
 * @category NewUserSubmitsElementorForm
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\ElementorPro\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'NewUserSubmitsElementorForm' ) ) :

	/**
	 * NewUserSubmitsElementorForm
	 *
	 * @category NewUserSubmitsElementorForm
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 */
	class NewUserSubmitsElementorForm {

		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'ElementorPro';

		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'new_user_submits_elementor_form';

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
				'label'         => __( 'User submits a form', 'suretriggers' ),
				'action'        => 'new_user_submits_elementor_form',
				'common_action' => 'elementor_pro/forms/new_record',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 2,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param object|array $record The record submitted.
		 * @param array        $handler The Ajax Handler component.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $record, $handler ) {
			if ( empty( $record ) ) {
				return;
			}

			if ( is_object( $record ) && method_exists( $record, 'get' ) ) {
				foreach ( $record->get( 'fields' ) as $key => $value ) {
					$context[ str_replace( ' ', '_', strtolower( isset( $value['title'] ) ? $value['title'] : $value['id'] ) ) ] = $value['value'];
				}

				$context['form_id'] = $record->get( 'form_settings' )['form_post_id'] . '_' . $record->get( 'form_settings' )['id'];

				$context['form_name'] = $record->get( 'form_settings' )['form_name'];

				AutomationController::sure_trigger_handle_trigger(
					[
						'trigger' => $this->trigger,
						'context' => $context,
					]
				);
			}
		}
	}

	NewUserSubmitsElementorForm::get_instance();

endif;
