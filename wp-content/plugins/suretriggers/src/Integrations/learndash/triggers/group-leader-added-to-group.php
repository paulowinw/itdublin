<?php
/**
 * GroupLeaderAddedToLDGroup.
 * php version 5.6
 *
 * @category GroupLeaderAddedToLDGroup
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\LearnDash\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'GroupLeaderAddedToLDGroup' ) ) :


	/**
	 * GroupLeaderAddedToLDGroup
	 *
	 * @category GroupLeaderAddedToLDGroup
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 */
	class GroupLeaderAddedToLDGroup {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'LearnDash';

		/**
		 * Action name.
		 *
		 * @var string
		 */
		public $trigger = 'group_leader_added_to_ld_group';

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
		 * Register a action.
		 *
		 * @param array $triggers actions.
		 * @return array
		 */
		public function register( $triggers ) {

			$triggers[ $this->integration ][ $this->trigger ] = [
				'label'         => __( 'User Added in Group', 'suretriggers' ),
				'action'        => 'group_leader_added_to_ld_group',
				'common_action' => 'ld_added_leader_group_access',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 2,
			];

			return $triggers;

		}

		/**
		 * Trigger listener
		 *
		 * @param int $user_id            User ID.
		 * @param int $group_id          Course ID.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $user_id, $group_id ) {
			if ( empty( $group_id ) || empty( $user_id ) ) {
				return;
			}

			$context                             = WordPress::get_user_context( $user_id );
			$context['sfwd_group_id']            = $group_id;
			$context['group_title']              = get_the_title( $group_id );
			$context['group_url']                = get_permalink( $group_id );
			$context['group_featured_image_id']  = get_post_meta( $group_id, '_thumbnail_id', true );
			$context['group_featured_image_url'] = get_the_post_thumbnail_url( $group_id );

			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger' => $this->trigger,
					'context' => $context,
				]
			);
		}
	}

	GroupLeaderAddedToLDGroup::get_instance();

endif;
