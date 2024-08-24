<?php
/**
 * RevokePointsUser.
 * php version 5.6
 *
 * @category RevokePointsUser
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
 * RevokePointsUser
 *
 * @category RevokePointsUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class RevokePointsUser extends AutomateAction {

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
	public $action = 'revoke_points_user';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Revoke Points from the User', 'suretriggers' ),
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

		$points_type = $selected_options['point_type'];

		$points = $selected_options['points'];

		if ( empty( $points ) ) {
			return false;
		}

		$deduct_points = 0;

		if ( function_exists( 'gamipress_get_user_points' ) ) {
			$points_post = get_post( $points_type );
			if ( is_object( $points_post ) ) {
				if ( property_exists( $points_post, 'post_name' ) ) {
					$point_type_name = $points_post->post_name;
					$existing_points = gamipress_get_user_points( absint( $user_id ), $point_type_name );
					if ( ( $existing_points - absint( $points ) ) < 0 ) {
						$deduct_points = absint( $points ) + ( $existing_points - absint( $points ) );
					} else {
						$deduct_points = absint( $points );
					}

					if ( function_exists( 'gamipress_deduct_points_to_user' ) ) {
						gamipress_deduct_points_to_user( absint( $user_id ), absint( $deduct_points ), $point_type_name );
					}
					$existing_points           = gamipress_get_user_points( absint( $user_id ), $point_type_name );
					$context                   = [];
					$context['points']         = $selected_options['points'];
					$context['point_type']     = get_the_title( $selected_options['point_type'] );
					$context['current_points'] = $existing_points;
			
					return array_merge(
						WordPress::get_user_context( $user_id ),
						$context
					);
				}
			}
		} else {
			throw new Exception( 'Something went wrong.' );
		}
	}
}

RevokePointsUser::get_instance();
