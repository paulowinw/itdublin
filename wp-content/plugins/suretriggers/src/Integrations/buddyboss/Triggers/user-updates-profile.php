<?php
/**
 * UserUpdatesProfile.
 * php version 5.6
 *
 * @category UserUpdatesProfile
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\BuddyBoss\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'UserUpdatesProfile' ) ) :
	/**
	 * UserUpdatesProfile
	 *
	 * @category UserUpdatesProfile
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class UserUpdatesProfile {

		use SingletonLoader;

		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'BuddyBoss';

		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'bb_user_updates_profile';

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
		 * @param array $triggers triggers.
		 *
		 * @return array
		 */
		public function register( $triggers ) {
			$triggers[ $this->integration ][ $this->trigger ] = [
				'label'         => __( 'User Updates Profile', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'xprofile_updated_profile',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 5,
			];

			return $triggers;
		}

		/**
		 *  Trigger listener
		 *
		 * @param int   $user_id User ID.
		 * @param array $posted_field_ids Posted Field IDs.
		 * @param bool  $errors Errors.
		 * @param array $old_values Old Values.
		 * @param array $new_values New Values.
		 *
		 * @return void
		 */
		public function trigger_listener( $user_id, $posted_field_ids, $errors, $old_values, $new_values ) {

			foreach ( $posted_field_ids as $field_id ) {
				if ( function_exists( 'xprofile_get_field' ) ) {
					$field                   = xprofile_get_field( $field_id );
					$context[ $field->name ] = $field->data->value;
				}
			}
			$user_data             = WordPress::get_user_context( $user_id );
			$context['user_id']    = $user_id;
			$context['user_email'] = $user_data['user_email'];
			foreach ( $user_data['user_role'] as $key => $role ) {
				$context['user_role'][ $key ] = $role;
			}
			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger' => $this->trigger,
					'context' => $context,
				]
			);
		}
	}

	UserUpdatesProfile::get_instance();
endif;
