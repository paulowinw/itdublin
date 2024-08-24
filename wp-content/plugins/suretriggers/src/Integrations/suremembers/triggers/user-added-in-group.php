<?php
/**
 * UserAddedInGroup.
 * php version 5.6
 *
 * @category UserAddedInGroup
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\SureMembers\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'UserAddedInGroup' ) ) :

	/**
	 * UserAddedInGroup
	 *
	 * @category UserAddedInGroup
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class UserAddedInGroup {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'SureMembers';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'suremember_user_added_in_group';

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
				'label'         => __( 'User Added', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'suremembers_after_access_grant',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 2,
			];

			return $triggers;

		}

		/**
		 * Trigger listener
		 *
		 * @param int   $user_id The user was added.
		 * @param array $access_group_ids The groupnids.
		 * @since 1.0.0
		 *
		 * @return array|void
		 */
		public function trigger_listener( $user_id, $access_group_ids ) {
			if ( empty( $user_id ) ) {
				return;
			}
			$context = [];
			foreach ( $access_group_ids as $group_id ) {
				$context             = WordPress::get_user_context( $user_id );
				$context['group']    = WordPress::get_post_context( $group_id );
				$context['group_id'] = $group_id;
				unset( $context['group']['ID'] );
			}

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
	UserAddedInGroup::get_instance();

endif;
