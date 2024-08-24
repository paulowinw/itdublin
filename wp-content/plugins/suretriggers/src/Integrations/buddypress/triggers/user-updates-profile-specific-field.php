<?php
/**
 * UserUpdatesProfileSpecificField.
 * php version 5.6
 *
 * @category UserUpdatesProfileSpecificField
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

if ( ! class_exists( 'UserUpdatesProfileSpecificField' ) ) :

	/**
	 * UserUpdatesProfileSpecificField
	 *
	 * @category UserUpdatesProfileSpecificField
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class UserUpdatesProfileSpecificField {


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
		public $trigger = 'user_updates_profile_specific_field';

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
				'label'         => __( 'A user updates their profile in a specific field', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'xprofile_updated_profile',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 5,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param int    $user_id User ID.
		 * @param array  $posted_field_ids Post field ids.
		 * @param bool   $errors Error.
		 * @param string $old_values Old Values.
		 * @param string $new_values New values.
		 * @return void
		 */
		public function trigger_listener( $user_id, $posted_field_ids, $errors, $old_values, $new_values ) {

			foreach ( $posted_field_ids as $field_id ) {
				$context['bp_field'] = $field_id;
				if ( function_exists( 'xprofile_get_field_data' ) ) {
					$value                  = xprofile_get_field_data( $field_id, $user_id );
					$context['field_value'] = $value;
					if ( function_exists( 'xprofile_get_field' ) ) {
						$field                   = xprofile_get_field( $context['bp_field'] );
						$context[ $field->name ] = $value;
					}
				}
				$context['user'] = WordPress::get_user_context( $user_id );
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
	UserUpdatesProfileSpecificField::get_instance();

endif;
