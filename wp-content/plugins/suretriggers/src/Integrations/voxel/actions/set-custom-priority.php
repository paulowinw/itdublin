<?php
/**
 * SetCustomPriority.
 * php version 5.6
 *
 * @category SetCustomPriority
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\Voxel\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use Exception;

/**
 * SetCustomPriority
 *
 * @category SetCustomPriority
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class SetCustomPriority extends AutomateAction {

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
	public $action = 'voxel_set_custom_priority';

	use SingletonLoader;

	/**
	 * Register action.
	 *
	 * @param array $actions action data.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Set Custom Priority', 'suretriggers' ),
			'action'   => 'voxel_set_custom_priority',
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
		$post_id = $selected_options['post_id'];

		// Get the priority.
		$priority = (int) $selected_options['priority'];

		if ( ! class_exists( 'Voxel\Post' ) || ! function_exists( 'Voxel\clamp' ) ) {
			return false;
		}

		// Get the post.
		$post = \Voxel\Post::force_get( $post_id );

		if ( ! $post ) {
			throw new Exception( 'Post not found' );
		}

		// Set the priority.
		$custom_priority = $priority;
		$custom_priority = \Voxel\clamp( $custom_priority, -128, 127 );

		if ( 0 !== $custom_priority ) {
			update_post_meta( $post->get_id(), 'voxel:priority', $custom_priority );

			// Reindex post.
			$post->should_index() ? $post->index() : $post->unindex();
		}

		return [
			'success'  => true,
			'message'  => esc_attr__( 'Custom priority set successfully', 'suretriggers' ),
			'post_id'  => $post_id,
			'priority' => $priority,
		];
	}

}

SetCustomPriority::get_instance();
