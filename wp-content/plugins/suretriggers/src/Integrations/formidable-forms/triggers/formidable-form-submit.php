<?php
/**
 * FormidableFormSubmit.
 * php version 5.6
 *
 * @category FormidableFormSubmit
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\FormidableForms\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;
use FrmEntryMeta;

if ( ! class_exists( 'FormidableFormSubmit' ) ) :

	/**
	 * FormidableFormSubmit
	 *
	 * @category FormidableFormSubmit
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class FormidableFormSubmit {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'FormidableForms';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'ff_form_submit';

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
				'common_action' => 'frm_after_create_entry',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 20,
				'accepted_args' => 2,
			];
			return $triggers;

		}

		/**
		 * Trigger listener
		 *
		 * @param int $entry_id Entry ID.
		 * @param int $form_id Form ID.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $entry_id, $form_id ) {
			global $wpdb;
			if ( empty( $entry_id ) ) {
				return;
			}

			if ( class_exists( '\FrmEntryMeta' ) ) {
				$metas = FrmEntryMeta::get_entry_meta_info( $entry_id );

				$data = [];
				
				foreach ( $metas as $meta ) {
					$field_id   = $meta->field_id;
					$field_name = $wpdb->get_var( $wpdb->prepare( 'SELECT name FROM ' . $wpdb->prefix . 'frm_fields WHERE id=%d', $field_id ) );
					$meta_data  = unserialize( $meta->meta_value );
					if ( false !== $meta_data ) {
						$data_val = unserialize( $meta->meta_value );
					} else {
						$data_val = $meta->meta_value;
					}
					if ( is_array( $data_val ) ) {
						foreach ( $data_val as $key => $val ) {
							$data[ $key ] = $val;
						}
					} else {
						$data[ $field_name ] = $data_val;
					}               
				}
			} else {
				$data = [];
			}

			$context['entry_id'] = $entry_id;
			$context['entry']    = $data;
			$user_id             = ap_get_current_user_id();
			if ( is_int( $user_id ) ) {
				$context['user'] = WordPress::get_user_context( $user_id );
			}
			$context['formidable_form'] = (int) $form_id;

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
	FormidableFormSubmit::get_instance();

endif;
