<?php
/**
 * AwardAchivementUser.
 * php version 5.6
 *
 * @category AwardAchivementUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\GamiPress\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

/**
 * AwardAchivementUser
 *
 * @category AwardAchivementUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class AwardAchivementUser extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'GamiPress';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'award_achivement_user';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Award Achievement to User', 'suretriggers' ),
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
	 * @throws Exception Exception.
	 *
	 * @return bool|array 
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		if ( empty( $user_id ) ) {
			return false;
		}

		$achievement_id = $selected_options['award'];

		if ( empty( $achievement_id ) ) {
			return false;
		}

		if ( function_exists( 'gamipress_achievement_user_exceeded_max_earnings' ) ) {
			$earned = gamipress_achievement_user_exceeded_max_earnings( $user_id, $achievement_id );
			if ( $earned ) {
				throw new Exception( 'Achievement maximum earnings reached.' );
			}
		}

		if ( function_exists( 'gamipress_award_achievement_to_user' ) ) {
			gamipress_award_achievement_to_user( absint( $achievement_id ), absint( $user_id ), ap_get_current_user_id() );
		}

		$context             = [];
		$context['award_id'] = $selected_options['award'];
		$context['award']    = get_the_title( $selected_options['award'] );

		return array_merge(
			WordPress::get_user_context( $user_id ),
			$context
		);
	}
}

AwardAchivementUser::get_instance();
