<?php
/**
 * UserProfileTypeSetToSpecificType.
 * php version 5.6
 *
 * @category UserProfileTypeSetToSpecificType
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

if ( ! class_exists( 'UserProfileTypeSetToSpecificType' ) ) :
	/**
	 * UserProfileTypeSetToSpecificType
	 *
	 * @category UserProfileTypeSetToSpecificType
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class UserProfileTypeSetToSpecificType {

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
		public $trigger = 'user_profile_type_changed';

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
				'label'         => __( 'User Profile Type Set To Specific Type', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'bp_set_member_type',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 3,
			];

			return $triggers;
		}

		/**
		 *  Trigger listener
		 *
		 * @param int    $user_id User ID.
		 * @param string $member_type Member type.
		 * @param array  $append Append.
		 *
		 * @return void
		 */
		public function trigger_listener( $user_id, $member_type, $append ) {

			if ( empty( $member_type ) ) {
				return;
			}

			// Get post id of selected profile type.
			if ( function_exists( 'bp_member_type_post_by_type' ) ) {
				$post_id = bp_member_type_post_by_type( $member_type );
			}

			if ( empty( $post_id ) ) {
				return;
			}

			$context                    = WordPress::get_user_context( $user_id );
			$context['bb_profile_type'] = $post_id;
			
			$context['bb_profile_type_name'] = get_post_meta( $post_id, '_bp_member_type_label_singular_name', true );  
			
			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger'    => $this->trigger,
					'wp_user_id' => $user_id,
					'context'    => $context,
				]
			);
		}
	}

	UserProfileTypeSetToSpecificType::get_instance();
endif;
