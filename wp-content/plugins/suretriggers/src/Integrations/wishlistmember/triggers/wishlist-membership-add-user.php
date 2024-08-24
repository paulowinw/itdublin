<?php
/**
 * WishlistMembershipAddUser.
 * php version 5.6
 *
 * @category WishlistMembershipAddUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\WishlistMember\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\WishlistMember\WishlistMember;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'WishlistMembershipAddUser' ) ) :

	/**
	 * WishlistMembershipAddUser
	 *
	 * @category WishlistMembershipAddUser
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class WishlistMembershipAddUser {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'WishlistMember';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'wishlist_membership_add_user';

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
				'label'         => __( 'User Added to Membership Level', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'wishlistmember_add_user_levels',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 2,
			];

			return $triggers;

		}

		/**
		 * Trigger listener
		 *
		 * @param integer $user_id The entry that was just created.
		 * @param array   $level_id The current form.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $user_id, $level_id ) {

			if ( ! $user_id ) {
				$user_id = ap_get_current_user_id();
			}
			if ( empty( $user_id ) ) {
				return;
			}

			if ( empty( $level_id ) ) {
				return;
			}
			$level_id = is_int( $level_id ) ? $level_id : reset( $level_id );
			$context  = array_merge(
				WordPress::get_user_context( $user_id ),
				WishlistMember::get_membership_detail_context( $level_id, $user_id )
			);

			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger'    => $this->trigger,
					'wp_user_id' => $user_id,
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
	WishlistMembershipAddUser::get_instance();

endif;
