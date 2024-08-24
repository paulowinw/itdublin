<?php
/**
 * RemoveAccessFromGroup.
 * php version 5.6
 *
 * @category RemoveAccessFromGroup
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\SureMembers\Actions;

use SureMembers\Inc\Helper;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

/**
 * RemoveAccessFromGroup
 *
 * @category RemoveAccessFromGroup
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class RemoveAccessFromGroup extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'SureMembers';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'remove_access_from_group';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Remove User from Group', 'suretriggers' ),
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
	 * @psalm-suppress UndefinedMethod
	 *
	 * @return array|bool|void
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		if ( ! $user_id ) {
			$this->set_error(
				[
					'msg' => __( 'User Not found', 'suretriggers' ),
				]
			);
			return false;
		}

		$access_group_id = $selected_options['st_access_group'];

		if ( empty( $access_group_id ) ) {
			return;
		}

		$helper = new Helper();
		$helper->revoke_access( $user_id, $access_group_id );

		$context['group'] = WordPress::get_post_context( $access_group_id );
		return $context;
	}

}

RemoveAccessFromGroup::get_instance();
