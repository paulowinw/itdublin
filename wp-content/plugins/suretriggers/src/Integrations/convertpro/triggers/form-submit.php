<?php
/**
 * ConvertProFormSubmit.
 * php version 5.6
 *
 * @category ConvertProFormSubmit
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\ConvertPro\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'ConvertProFormSubmit' ) ) :

	/**
	 * ConvertProFormSubmit
	 *
	 * @category ConvertProFormSubmit
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class ConvertProFormSubmit {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'ConvertPro';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'convert_pro_form_submit';

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
				'common_action' => 'cpro_form_submit',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 2,
			];
			return $triggers;

		}

		/**
		 * Trigger listener
		 *
		 * @param array $response Response Data.
		 * @param array $post_data Post Data.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $response, $post_data ) {
			if ( empty( $response ) ) {
				return;
			}

			$param      = [];
			$return_arr = [];
			$style_id   = isset( $post_data['style_id'] ) ? (int) sanitize_text_field( esc_attr( $post_data['style_id'] ) ) : '';
			$email_meta = get_post_meta( $style_id, 'connect', true );
			$email_meta = ( ! empty( $email_meta ) ) ? call_user_func_array( 'array_merge', $email_meta ) : [];
			$data       = json_decode( $email_meta['map_placeholder'] );

			foreach ( $data as $value ) {
				$return_arr[ $value->name ] = $value->value;
			}
			if ( is_array( $post_data['param'] ) && count( $post_data['param'] ) ) {
				foreach ( $post_data['param'] as $key => $value ) {
					$k                        = isset( $return_arr[ $key ] ) ? $return_arr[ $key ] : $key;
					$context[ ucfirst( $k ) ] = $value;
				}
			}
			$context['convertpro_form'] = (int) $style_id;

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
	ConvertProFormSubmit::get_instance();

endif;
