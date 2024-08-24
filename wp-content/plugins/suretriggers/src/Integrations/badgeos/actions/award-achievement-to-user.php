<?php
/**
 * AwardAchievementToUser.
 * php version 5.6
 *
 * @category AwardAchievementToUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\BadgeOS\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

/**
 * AwardAchievementToUser
 *
 * @category AwardAchievementToUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class AwardAchievementToUser extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'BadgeOS';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'award_achievement_to_user';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Award a Achievement to the user', 'suretriggers' ),
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
	 * @return bool|array 
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		if ( empty( $user_id ) ) {
			return false;
		}

		$achievement_id = $selected_options['badgeos_rank'];

		if ( empty( $achievement_id ) ) {
			return false;
		}

		badgeos_award_achievement_to_user( absint( $achievement_id ), absint( $user_id ) );

		$context                        = [];
		$context['achievement_type_id'] = $selected_options['achievement_type'];
		$context['achievement_type']    = get_the_title( $selected_options['achievement_type'] );
		$context['achievement_id']      = $selected_options['badgeos_rank'];
		$context['achievement']         = get_the_title( $selected_options['badgeos_rank'] );

		return array_merge(
			WordPress::get_user_context( $user_id ),
			$context
		);
	}
}

AwardAchievementToUser::get_instance();
