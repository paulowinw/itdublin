<?php
/**
 * UserJoinsPublicGroup.
 * php version 5.6
 *
 * @category UserJoinsPublicGroup
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

if ( ! class_exists( 'UserJoinsPublicGroup' ) ) :
	/**
	 * UserJoinsPublicGroup
	 *
	 * @category UserJoinsPublicGroup
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class UserJoinsPublicGroup {

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
		public $trigger = 'user_joins_public_group';

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
				'label'         => __( 'User Joins Public Group.', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'groups_join_group',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 2,
			];

			return $triggers;
		}

		/**
		 *  Trigger listener
		 *
		 * @param int $group_id group id.
		 * @param int $user_id user id.
		 *
		 * @return void
		 */
		public function trigger_listener( $group_id, $user_id ) {
			$context = WordPress::get_user_context( $user_id );
			$avatar  = get_avatar_url( $user_id );
			$group   = groups_get_group( $group_id );

			if ( 'public' === $group->status ) {
				$context['avatar_url']        = ( $avatar ) ? $avatar : '';
				$context['group_id']          = ( property_exists( $group, 'id' ) ) ? (int) $group->id : '';
				$context['group_name']        = ( property_exists( $group, 'name' ) ) ? $group->name : '';
				$context['group_description'] = ( property_exists( $group, 'description' ) ) ? $group->description : '';

				AutomationController::sure_trigger_handle_trigger(
					[
						'trigger'    => $this->trigger,
						'wp_user_id' => $user_id,
						'context'    => $context,
					]
				);
			}
		}
	}

	UserJoinsPublicGroup::get_instance();
endif;
