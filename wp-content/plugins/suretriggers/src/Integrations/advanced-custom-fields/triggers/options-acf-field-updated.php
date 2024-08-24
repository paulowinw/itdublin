<?php
/**
 * OptionsAcfFieldUpdated.
 * php version 5.6
 *
 * @category OptionsAcfFieldUpdated
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\AdvancedCustomFields\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;

if ( ! class_exists( 'OptionsAcfFieldUpdated' ) ) :

	/**
	 * OptionsAcfFieldUpdated
	 *
	 * @category OptionsAcfFieldUpdated
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class OptionsAcfFieldUpdated {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'AdvancedCustomFields';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'options_acf_field_updated';

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
				'label'         => __( 'Field Updated On Options Page', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'acf/save_post',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 1,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param int $post_id Post ID.
		 * @return void|bool
		 */
		public function trigger_listener( $post_id ) {
			if ( 'options' !== $post_id ) {
				return;
			}

			if ( ! function_exists( 'get_fields' ) ) {
				return;
			}
			
			$field_name = 'all';
			$get_value  = get_fields( $post_id );

			if ( isset( $get_value[ $field_name ] ) ) {
				$acf_value = $get_value[ $field_name ];
			} else {
				$acf_value = $get_value;
			}
			$response_array = [];
			if ( is_array( $acf_value ) ) {
				if ( isset( $acf_value[0] ) && is_array( $acf_value[0] ) ) {
					$response_array[ $field_name ] = wp_json_encode( $acf_value );
					$response_array['field_id']    = $field_name;
				} else {
					foreach ( $acf_value as $key => $value ) {
						if ( is_array( $value ) ) {
							$response_array[ $key ] = wp_json_encode( $value );
						} else {
							$response_array[ $key ] = $value;
						}
						$response_array['field_id'] = $key;
					}
				}
			} else {
				$response_array[ $field_name ] = $acf_value;
			}
			$context = $response_array;

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
	OptionsAcfFieldUpdated::get_instance();

endif;
