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

namespace SureTriggers\Integrations\LearnDashAchievements\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;
use LearnDash\Achievements\Database;
use Exception;

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
	public $integration = 'LearnDashAchievements';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'learndash_award_achievement_to_user';

	use SingletonLoader;

	/**
	 * Register an action.
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
	 * @param int   $user_id user id.
	 * @param int   $automation_id automation_id.
	 * @param array $fields template fields.
	 * @param array $selected_options saved template data.
	 * @throws Exception Exception.
	 *
	 * @return bool|array
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
	
		$award_id = $selected_options['achievement_id'];

		$user_email = $selected_options['wp_user_email'];

		$award = get_post( $award_id );

		if ( is_email( $user_email ) ) {
			$user = get_user_by( 'email', $user_email );

			if ( $user ) {
				$user_id = $user->ID;
				if ( class_exists( '\LearnDash\Achievements\Achievement' ) ) {
					/**
					 *
					 * Ignore line
					 *
					 * @phpstan-ignore-next-line
					 */
					if ( method_exists( '\LearnDash\Achievements\Achievement', 'store' ) ) {
						$stored = \LearnDash\Achievements\Achievement::store( $award, $user_id );
						if ( false === $stored ) {
							throw new Exception( 'Something went wrong.' );
						}
						$context = WordPress::get_user_context( $user_id );
						if ( class_exists( '\Database' ) ) {
							$achievements = \Database::get_user_achievements( $user_id );
							foreach ( $achievements as $value ) {
								foreach ( $value as $key => $val ) {
									$context['achievement'][ $key ] = $val;
									if ( 'post_id' == $key ) {
										$context['achievement_title'] = get_the_title( $val );
									}   
								}
							}
						}
						return $context;
					}
					throw new Exception( 'Store method not exists.' );

				} else {
					throw new Exception( 'Achievement class not found.' );
				}
			} else {
				throw new Exception( 'User not found.' );
			}
		} else {
			$error = [
				'status'   => esc_attr__( 'Error', 'suretriggers' ),
				'response' => esc_attr__( 'Please enter valid email address.', 'suretriggers' ),
			];

			return $error;
		}
	}

}

AwardAchievementToUser::get_instance();
