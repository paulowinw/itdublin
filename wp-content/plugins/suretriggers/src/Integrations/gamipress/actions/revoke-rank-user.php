<?php
/**
 * RevokeRankUser.
 * php version 5.6
 *
 * @category RevokeRankUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\GamiPress\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;
use Exception;

/**
 * RevokeRankUser
 *
 * @category RevokeRankUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class RevokeRankUser extends AutomateAction {

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
	public $action = 'revoke_rank_user';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Revoke Rank to User', 'suretriggers' ),
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

		if ( function_exists( 'gamipress_get_rank_types' ) ) {
			$rank_types = gamipress_get_rank_types();
		}

		$rank = $selected_options['rank'];
		$rank = get_post( $rank );

		if ( ! $rank || ! isset( $rank_types[ $rank->post_type ] ) ) {
			return;
		}

		if ( function_exists( 'gamipress_get_user_rank_id' ) ) {
			$user_rank_id = gamipress_get_user_rank_id( absint( $user_id ), $rank->post_type );
		}

		if ( ! empty( $user_rank_id ) && $rank->ID == $user_rank_id ) {
			if ( function_exists( 'gamipress_revoke_rank_to_user' ) ) {
				gamipress_revoke_rank_to_user( absint( $user_id ), $user_rank_id, 0, [ 'admin_id' => absint( $user_id ) ] );
			}
			// if still rank is assigned to user.
			if ( function_exists( 'gamipress_get_user_rank_id' ) ) {
				$user_rank_id = gamipress_get_user_rank_id( absint( $user_id ), $rank->post_type );
				if ( ! empty( $user_rank_id ) && $rank->ID == $user_rank_id ) {
					$meta = "_gamipress_{$rank->post_type}_rank";
					if ( function_exists( 'gamipress_delete_user_meta' ) ) {
						gamipress_delete_user_meta( $user_id, $meta );
					}
				}
			}
			$context              = [];
			$context['rank_type'] = $selected_options['rank_type'];
			$context['rank_id']   = $selected_options['rank'];
			$context['rank']      = get_the_title( $selected_options['rank'] );

			return array_merge(
				WordPress::get_user_context( $user_id ),
				$context
			);
		} else {
			throw new Exception( "The user didn't have the specified rank." );
		}
	}
}

RevokeRankUser::get_instance();
