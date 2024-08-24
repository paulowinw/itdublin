<?php
/**
 * AwardPointsUser.
 * php version 5.6
 *
 * @category AwardPointsUser
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
 * AwardPointsUser
 *
 * @category AwardPointsUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class AwardPointsUser extends AutomateAction {

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
	public $action = 'award_points_user';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Award Points to User', 'suretriggers' ),
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
		$points      = $selected_options['points'];

		if ( empty( $points ) ) {
			return false;
		}

		if ( function_exists( 'gamipress_update_user_points' ) ) {
			$args        = [];
			$args        = wp_parse_args(
				$args,
				[
					'admin_id'       => 0,
					'achievement_id' => null,
					'reason'         => '',
					'log_type'       => '',
				] 
			);
			$points_type = get_post( $points_type );
			if ( is_object( $points_type ) ) {
				if ( property_exists( $points_type, 'post_name' ) ) {
					gamipress_update_user_points( $user_id, $points, $args['admin_id'], $args['achievement_id'], $points_type->post_name, $args['reason'], $args['log_type'] );
				}
			}
		}
		$context = [];
		if ( function_exists( 'gamipress_get_user_points' ) ) {
			$points_type = get_post( $points_type );
			if ( is_object( $points_type ) ) {
				if ( property_exists( $points_type, 'post_name' ) ) {
					$current_points            = gamipress_get_user_points( $user_id, $points_type->post_name );
					$context['current_points'] = $current_points;
				}
			}
		}
		$context['point_type'] = get_the_title( $selected_options['point_type'] );

		return array_merge(
			WordPress::get_user_context( $user_id ),
			$context
		);
		
	}
}

AwardPointsUser::get_instance();
