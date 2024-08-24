<?php
/**
 * CreateComment
 * php version 5.6
 *
 * @category CreateComment
 * @package  SureTriggers
 * @author   BSF <tapand@bsf.io>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\Wordpress\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use Exception;

/**
 * CreateComment
 *
 * @category CreateComment
 * @package  SureTriggers
 * @author   BSF <tapand@bsf.io>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class CreateComment extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'WordPress';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'wp_create_comment';

	use SingletonLoader;

	/**
	 * Register an action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Create Comment', 'suretriggers' ),
			'action'   => 'wp_create_comment',
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
	 * @return \WP_Comment|null|bool
	 * @throws Exception Exception.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		$result_arr = [];
		foreach ( $fields as $field ) {
			$result_arr[ $field['name'] ] = isset( $selected_options[ $field['name'] ] ) ? $selected_options[ $field['name'] ] : '';
		}

		$comment_id = wp_new_comment( $result_arr );

		if ( ! $comment_id || is_wp_error( $comment_id ) ) {
			throw new Exception( 'Failed to insert comment' );
		}

		return get_comment( $comment_id );
	}
}

CreateComment::get_instance();
