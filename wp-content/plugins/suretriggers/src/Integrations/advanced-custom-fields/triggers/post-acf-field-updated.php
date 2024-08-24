<?php
/**
 * PostAcfFieldUpdated.
 * php version 5.6
 *
 * @category PostAcfFieldUpdated
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

if ( ! class_exists( 'PostAcfFieldUpdated' ) ) :

	/**
	 * PostAcfFieldUpdated
	 *
	 * @category PostAcfFieldUpdated
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class PostAcfFieldUpdated {


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
		public $trigger = 'post_acf_field_updated';

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
				'label'         => __( 'Field Updated On Post', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'updated_post_meta',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 99,
				'accepted_args' => 4,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param int $meta_id  Meta ID.
		 * @param int $post_id  Post ID.
		 * @param int $meta_key  Meta Key.
		 * @param int $meta_value  Meta Value.
		 * @return void|bool
		 */
		public function trigger_listener( $meta_id, $post_id, $meta_key, $meta_value ) {

			// Check if updated meta key is not edit_lock and current action.
			if ( 'updated_post_meta' === current_action() && '_edit_lock' !== $meta_key ) {
				if ( ! is_int( $post_id ) ) {
					return;
				}

				$post_data = $_POST; // @codingStandardsIgnoreLine

				// Check and update $_POST data.
				if ( ! empty( $post_data['acf'] ) && isset( $post_data['acf'] ) ) {
					if ( function_exists( 'get_fields' ) ) {
						$fields = get_fields( $post_id );
						if ( is_array( $fields ) && ! empty( $fields ) ) {
							$fields_keys = array_keys( $fields );
							if ( ! in_array( $meta_key, $fields_keys, true ) ) {
								return;
							}
						}

						$context['field_id'] = $meta_key;
						if ( function_exists( 'get_field' ) ) {
							$context[ $meta_key ] = get_field( $meta_key, $post_id );
						}
						$context['post_fields'] = $fields;
					}
				} else {
					return;
				}
				$context['post']         = WordPress::get_post_context( $post_id );
				$context['wp_post']      = $post_id;
				$context['wp_post_type'] = get_post_type( $post_id );

				AutomationController::sure_trigger_handle_trigger(
					[
						'trigger' => $this->trigger,
						'context' => $context,
					]
				);
			}
		}
	}

	/**
	 * Ignore false positive
	 *
	 * @psalm-suppress UndefinedMethod
	 */
	PostAcfFieldUpdated::get_instance();

endif;
