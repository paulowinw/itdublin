<?php
/**
 * RevokeAchivementUser.
 * php version 5.6
 *
 * @category RevokeAchivementUser
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
 * RevokeAchivementUser
 *
 * @category RevokeAchivementUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class RevokeAchivementUser extends AutomateAction {

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
	public $action = 'revoke_achivement_user';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Revoke Achievement from User', 'suretriggers' ),
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
	 * @return bool|array|void 
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		if ( empty( $user_id ) ) {
			return false;
		}

		$achievement_id    = $selected_options['award'];
		$achievement_types = $selected_options['achivement_type'];

		if ( empty( $achievement_id ) ) {
			return false;
		}

		if ( '-1' === $achievement_id && isset( $achievement_types ) ) {
			
			// Setup CT object.
			if ( function_exists( 'ct_setup_table' ) ) {
				$ct_table = ct_setup_table( 'gamipress_user_earnings' );
			}
			if ( class_exists( 'CT_Query' ) ) {
				$query                = new \CT_Query(
					[
						'no_found_rows'  => true,
						'post_type'      => $achievement_types,
						'user_id'        => $user_id,
						'post_id'        => 0,
						'items_per_page' => - 1,
					]
				);
				$results              = $query->get_results();
				$achievements_revoked = count( $results );
				if ( $achievements_revoked ) {
					foreach ( $results as $achievement ) {
						if ( function_exists( 'gamipress_revoke_achievement_to_user' ) ) {
							gamipress_revoke_achievement_to_user( absint( $achievement->post_id ), absint( $user_id ) );
						}
					}
	
					$context             = [];
					$context['award_id'] = $selected_options['award'];
					$context['award']    = get_the_title( $selected_options['award'] );
			
					// reset.
					if ( function_exists( 'ct_reset_setup_table' ) ) {
						ct_reset_setup_table();
					}
					return array_merge(
						WordPress::get_user_context( $user_id ),
						$context
					);
				} else {
					throw new Exception( "The user didn't have the specified achievement." );
				}
			}
		}

		// If the user has not already earned the achievement...
		if ( function_exists( 'gamipress_get_user_achievements' ) ) {
			if ( gamipress_get_user_achievements(
				[
					'user_id'        => absint( $user_id ),
					'achievement_id' => absint( $achievement_id ),
				]
			) ) {
				if ( function_exists( 'gamipress_revoke_achievement_to_user' ) ) {
					gamipress_revoke_achievement_to_user( absint( $achievement_id ), absint( $user_id ) );
				}
				$context             = [];
				$context['award_id'] = $selected_options['award'];
				$context['award']    = get_the_title( $selected_options['award'] );
		
				return array_merge(
					WordPress::get_user_context( $user_id ),
					$context
				);
			} else {
				throw new Exception( "The user didn't have the specified achievement." );
			}
		}
	}
}

RevokeAchivementUser::get_instance();
