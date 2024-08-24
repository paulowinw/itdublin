<?php
/**
 * UserRegistersSpecificFieldValue.
 * php version 5.6
 *
 * @category UserRegistersSpecificFieldValue
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\BuddyPress\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;

if ( ! class_exists( 'UserRegistersSpecificFieldValue' ) ) :

	/**
	 * UserRegistersSpecificFieldValue
	 *
	 * @category UserRegistersSpecificFieldValue
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class UserRegistersSpecificFieldValue {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'BuddyPress';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'user_registers_specific_field_value';

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
				'label'         => __( 'A user registers with specific field', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'bp_core_signup_user',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 1,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param int $user_id User ID.
		 * @return void
		 */
		public function trigger_listener( $user_id ) {

			global $wpdb;

			$base_group_id = 1;
			if ( function_exists( 'bp_xprofile_base_group_id' ) ) {
				$base_group_id = bp_xprofile_base_group_id();
			}

			$xprofile_fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}bp_xprofile_fields WHERE parent_id = 0 AND group_id = %d ORDER BY field_order ASC", $base_group_id ) );

			if ( ! empty( $xprofile_fields ) ) {
				foreach ( $xprofile_fields as $xprofile_field ) {
					$context['bp_field'] = $xprofile_field->id;
					if ( function_exists( 'xprofile_get_field_data' ) ) {
						$user_xprofile_field_value = xprofile_get_field_data( $context['bp_field'], $user_id );
						$context['field_value']    = $user_xprofile_field_value;   
						$context['user']           = WordPress::get_user_context( $user_id );
						AutomationController::sure_trigger_handle_trigger(
							[
								'trigger' => $this->trigger,
								'context' => $context,
							]
						);
					}
				}
			}
		}
	}

	/**
	 * Ignore false positive
	 *
	 * @psalm-suppress UndefinedMethod
	 */
	UserRegistersSpecificFieldValue::get_instance();

endif;
