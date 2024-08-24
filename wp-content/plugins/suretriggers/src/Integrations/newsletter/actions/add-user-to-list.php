<?php
/**
 * AddUserToList.
 * php version 5.6
 *
 * @category AddUserToList
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\Newsletter\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;
use Newsletter;

/**
 * AddUserToList
 *
 * @category AddUserToList
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class AddUserToList extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'Newsletter';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'add_user_to_list';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Add User to List', 'suretriggers' ),
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
		$list_ids   = $selected_options['list_id'];
		$user_email = $selected_options['wp_user_email'];
		
		if ( ! class_exists( 'Newsletter' ) ) {
			return;
		}

		if ( is_email( $user_email ) ) {
			$user = get_user_by( 'email', $user_email );

			if ( $user ) {
				$user_id    = $user->ID;
				$newsletter = Newsletter::instance();
				
				$subscriber_user = $newsletter->get_user( $user_email );
				if ( empty( $subscriber_user ) ) {
				
					$subscriber_user = [
						'wp_user_id' => $user_id,
						'email'      => $user_email,
					];
				}

				foreach ( $list_ids as $list ) {
					$subscriber_user[ $list['value'] ] = 1;
				}

				return $newsletter->save_user( $subscriber_user );
			}
		}
	}
}

AddUserToList::get_instance();
