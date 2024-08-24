<?php
/**
 * DeleteWallPost.
 * php version 5.6
 *
 * @category DeleteWallPost
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\Voxel\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;
use Exception;

/**
 * DeleteWallPost
 *
 * @category DeleteWallPost
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class DeleteWallPost extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'Voxel';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'voxel_delete_wall_post';

	use SingletonLoader;

	/**
	 * Register action.
	 *
	 * @param array $actions action data.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Delete Post Wall', 'suretriggers' ),
			'action'   => 'voxel_delete_wall_post',
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
	 * @throws Exception Exception.
	 * 
	 * @return bool|array
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		
		$post_id = (int) $selected_options['wall_post_id'];

		if ( ! class_exists( 'Voxel\Timeline\Status' ) ) {
			return false;
		}
		
		// Get the post.
		$post = \Voxel\Timeline\Status::get( $post_id );

		if ( ! $post ) {
			throw new Exception( 'Wall Post not found' );
		}

		// Delete the post.
		$post->delete();
		return [
			'success' => true,
			'message' => esc_attr__( 'Wall Post deleted successfully', 'suretriggers' ),
			'post_id' => $post_id,
		];
	}

}

DeleteWallPost::get_instance();
