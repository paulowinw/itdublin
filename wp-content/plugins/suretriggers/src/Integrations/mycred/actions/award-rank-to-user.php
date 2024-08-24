<?php
/**
 * AwardRankToUser.
 * php version 5.6
 *
 * @category AwardRankToUser
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
 * AwardRankToUser
 *
 * @category AwardRankToUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class AwardRankToUser extends AutomateAction {


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
	public $action = 'award_rank_to_user';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {

		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Award Rank', 'suretriggers' ),
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

		$rank_id = $selected_options['cred_rank'];

		if ( empty( $rank_id ) || empty( $user_id ) ) {
			return false;
		}

		$rank_detail = mycred_get_rank( $rank_id );
		mycred_save_users_rank( $user_id, $rank_id, $rank_detail->point_type->cred_id );

		return array_merge(
			WordPress::get_user_context( $user_id ),
			[
				'rank_id'  => $rank_detail->post_id,
				'title'    => $rank_detail->title,
				'minimum'  => $rank_detail->minimum,
				'maximum'  => $rank_detail->maximum,
				'users'    => $rank_detail->count,
				'logo_id'  => $rank_detail->logo_id,
				'logo_url' => $rank_detail->logo_url,
			]
		);
	}
}

AwardRankToUser::get_instance();
