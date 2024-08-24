<?php
/**
 * WfDeleteTopic.
 * php version 5.6
 *
 * @category WfDeleteTopic
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\wpForo\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use Exception;

/**
 * WfDeleteTopic
 *
 * @category WfDeleteTopic
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class WfDeleteTopic extends AutomateAction {


	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'wpForo';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'wp_foro_delete_topic';

	use SingletonLoader;

	/**
	 * Register an action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Delete Topic', 'suretriggers' ),
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
	
		$topic = $selected_options['topic_id'];
		if ( ! function_exists( 'WPF' ) ) {
			return false;
		}

		$topicid = WPF()->topic->delete( $topic, true, false );
		if ( $topicid ) {
			return [ 'message' => 'Topic deleted successfully.' ];
		} else {
			throw new Exception( 'Topic not deleted.' );
		}
	}

}

WfDeleteTopic::get_instance();
