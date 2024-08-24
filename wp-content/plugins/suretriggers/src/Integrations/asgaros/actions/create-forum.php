<?php
/**
 * CreateForum.
 * php version 5.6
 *
 * @category CreateForum
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\Asgaros\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;
use AsgarosForumAdmin;
use AsgarosForum;

/**
 * CreateForum
 *
 * @category CreateForum
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class CreateForum extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'Asgaros';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'asgaros_create_forum';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Create a group', 'suretriggers' ),
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
		if ( ! class_exists( 'AsgarosForum' ) ) {
			return false;
		}
		$asgaros_forum      = new AsgarosForum();
		$forum_order        = ( is_numeric( $selected_options['forum_order'] ) ) ? $selected_options['forum_order'] : 0;
		$forum_name         = $selected_options['forum_name'];
		$forum_parent_forum = $selected_options['forum_parent'];
		$forum_category     = $selected_options['forum_category'];
		$forum_icon         = sanitize_text_field( $selected_options['forum_icon'] );
		$forum_icon         = ( empty( $forum_icon ) ) ? 'fas fa-comments' : $forum_icon;
		$forum_status       = sanitize_key( $selected_options['forum_status'] );
		$forum_description  = sanitize_text_field( $selected_options['forum_description'] );

		$forum_id = $asgaros_forum->content->insert_forum( $forum_category, $forum_name, $forum_description, $forum_parent_forum, $forum_icon, $forum_order, $forum_status );
		$forum    = $asgaros_forum->content->get_forum( $forum_id );
		$user     = WordPress::get_user_context( $user_id );
		return array_merge( $forum, $user );
		
	}
}

CreateForum::get_instance();
