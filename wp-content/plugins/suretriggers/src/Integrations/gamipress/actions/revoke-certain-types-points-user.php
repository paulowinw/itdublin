<?php
/**
 * RevokeCertaintypesPointsUser.
 * php version 5.6
 *
 * @category RevokeCertaintypesPointsUser
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
 * RevokeCertaintypesPointsUser
 *
 * @category RevokeCertaintypesPointsUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class RevokeCertaintypesPointsUser extends AutomateAction {

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
	public $action = 'revoke_certain_types_points_user';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Revoke Certain Types of Points from User', 'suretriggers' ),
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

		$points_type = $selected_options['point_type'];

		if ( 'all' === $points_type ) {
			if ( function_exists( 'gamipress_get_points_types_slugs' ) ) {
				foreach ( gamipress_get_points_types_slugs() as $points_type ) {
					if ( function_exists( 'gamipress_get_user_points' ) ) {
						$deduct_points = gamipress_get_user_points( absint( $user_id ), $points_type );
						if ( function_exists( 'gamipress_deduct_points_to_user' ) ) {
							gamipress_deduct_points_to_user( absint( $user_id ), absint( $deduct_points ), $points_type );
							$points_type = $points_type->post_name;
							if ( ! empty( $points_type ) ) {
								$points_meta     = "_gamipress_{$points_type}_points";
								$new_points_meta = "_gamipress_{$points_type}_new_points";
								$total_points    = 0;
								$new_points      = 0;
								if ( function_exists( 'gamipress_update_user_meta' ) ) {
									gamipress_update_user_meta( $user_id, $points_meta, $total_points );
									gamipress_update_user_meta( $user_id, $new_points_meta, $new_points );
								}
							}
						}
					}
				}
			}
		} else {
			if ( function_exists( 'gamipress_get_user_points' ) ) {
				$deduct_points = gamipress_get_user_points( absint( $user_id ), $points_type );
				$points_type   = get_post( $points_type );
				if ( function_exists( 'gamipress_deduct_points_to_user' ) ) {
					gamipress_deduct_points_to_user( absint( $user_id ), absint( $deduct_points ), $points_type );
					if ( is_object( $points_type ) ) {
						$points_type = $points_type->post_name;
						if ( ! empty( $points_type ) ) {
							$points_meta     = "_gamipress_{$points_type}_points";
							$new_points_meta = "_gamipress_{$points_type}_new_points";
							$total_points    = 0;
							$new_points      = 0;
							if ( function_exists( 'gamipress_update_user_meta' ) ) {
								gamipress_update_user_meta( $user_id, $points_meta, $total_points );
								gamipress_update_user_meta( $user_id, $new_points_meta, $new_points );
							}
						}
					}
				}
			}
		}
		
		$context               = [];
		$context['point_type'] = get_the_title( $selected_options['point_type'] );

		return array_merge(
			WordPress::get_user_context( $user_id ),
			$context
		);      
	}
}

RevokeCertaintypesPointsUser::get_instance();
