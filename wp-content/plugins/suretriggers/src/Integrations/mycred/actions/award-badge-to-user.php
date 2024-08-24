<?php
/**
 * AwardBadgeToUser.
 * php version 5.6
 *
 * @category AwardBadgeToUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\MyCred\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

/**
 * AwardBadgeToUser
 *
 * @category AwardBadgeToUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class AwardBadgeToUser extends AutomateAction {


	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'MyCred';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'award_badge_to_user';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {

		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Award Badge', 'suretriggers' ),
			'action'   => $this->action,
			'function' => [ $this, 'action_listener' ],
		];

		return $actions;

	}


	/**
	 * Action listener.
	 *
	 * @param int   $user_id user_id.
	 * @param int   $automation_id automation_id.
	 * @param array $fields fields.
	 * @param array $selected_options selectedOptions.
	 *
	 * @return array
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {

		$badge_id = $selected_options['cred_badge'];

		if ( empty( $badge_id ) || empty( $user_id ) ) {
			return false;
		}

		// Get the badge object.
		$badge = mycred_get_badge( $badge_id );
		mycred_assign_badge_to_user( $user_id, $badge_id );

		return array_merge(
			WordPress::get_user_context( $user_id ),
			[
				'badge_id'  => $badge->post_id,
				'title'     => $badge->title,
				'earned_by' => $badge->earnedby,
			]
		);
	}
}

AwardBadgeToUser::get_instance();
