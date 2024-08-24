<?php
/**
 * UserCreatesGroup.
 * php version 5.6
 *
 * @category UserCreatesGroup
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

if ( ! class_exists( 'UserCreatesGroup' ) ) :
	/**
	 * UserCreatesGroup
	 *
	 * @category UserCreatesGroup
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class UserCreatesGroup {

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
		public $trigger = 'bb_user_creates_group';

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
				'label'         => __( 'User Creates Group', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'groups_group_create_complete',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 1,
			];

			return $triggers;
		}

		/**
		 *  Trigger listener
		 *
		 * @param int $group_id group id.
		 *
		 * @return void
		 */
		public function trigger_listener( $group_id ) {

			if ( ! function_exists( 'groups_get_group' ) || ! function_exists( 'bp_groups_get_group_type' ) || 
			! function_exists( 'bp_get_group_cover_url' ) || ! function_exists( 'bp_get_group_avatar_url' ) ||
			! function_exists( 'groups_get_group_members' ) || ! function_exists( 'groups_get_invites' ) ) {
				return;
			}
			
			$group = groups_get_group( $group_id );

			$context['group_id']            = ( property_exists( $group, 'id' ) ) ? (int) $group->id : '';
			$context['group_name']          = ( property_exists( $group, 'name' ) ) ? $group->name : '';
			$context['group_description']   = ( property_exists( $group, 'description' ) ) ? $group->description : '';
			$current_types                  = (array) bp_groups_get_group_type( $group_id, false );
			$context['group_type']          = $current_types;
			$context['group_status']        = $group->status;
			$context['group_date_created']  = $group->date_created;
			$context['group_enabled_forum'] = $group->enable_forum;
			$context['group_cover_url']     = bp_get_group_cover_url( $group );
			$context['group_avatar_url']    = bp_get_group_avatar_url( $group );
			$context['group_creator']       = WordPress::get_user_context( $group->creator_id );
			$members                        = groups_get_group_members(
				[
					'group_id' => $group_id,
				]
			);
			foreach ( $members['members'] as $key => $member ) {
				$context['group_member'][ $key ] = WordPress::get_user_context( $member->ID );
			}
			$args        = [ 'item_id' => $group_id ];
			$invitations = groups_get_invites( $args );
			if ( ! empty( $invitations ) ) {
				foreach ( $invitations as $key => $invite ) {
					$context['invitation'][ $key ] = $invite;
				}
			}

			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger' => $this->trigger,
					'context' => $context,
				]
			);
		}
	}

	UserCreatesGroup::get_instance();
endif;
