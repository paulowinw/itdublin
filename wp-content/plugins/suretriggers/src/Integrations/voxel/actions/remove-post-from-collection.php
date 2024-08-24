<?php
/**
 * RemovePostFromCollection.
 * php version 5.6
 *
 * @category RemovePostFromCollection
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
 * RemovePostFromCollection
 *
 * @category RemovePostFromCollection
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class RemovePostFromCollection extends AutomateAction {

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
	public $action = 'voxel_remove_post_from_collection';

	use SingletonLoader;

	/**
	 * Register action.
	 *
	 * @param array $actions action data.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Remove Post from Collection', 'suretriggers' ),
			'action'   => 'voxel_remove_post_from_collection',
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
		// Get the collection ID.
		$collection_id = (int) $selected_options['collection_post_id'];

		// Get the post ID.
		$post_id = (int) $selected_options['post_id'];

		if ( ! class_exists( 'Voxel\Post' ) ) {
			return false;
		}

		// Get the post.
		$post = \Voxel\Post::force_get( $post_id );

		if ( ! $post ) {
			throw new Exception( 'Post not found' );
		}

		// Get the collection.
		$collection = \Voxel\Post::force_get( $collection_id );

		if ( ! $collection ) {
			throw new Exception( 'Collection not found' );
		}

		// Get the items field.
		$field = $collection->get_field( 'items' );

		// Get previous items.
		$items = $field->get_value();

		// If items are not available, return error.
		if ( ! $items ) {
			throw new Exception( 'Collection items not found' );
		}

		// If the post is not in the collection, then skip.
		if ( ! in_array( $post_id, $items, true ) ) {
			throw new Exception( 'Post not found in collection' );
		}

		// Remove the post from the items.
		$items = array_diff( $items, [ $post_id ] );

		// Add the post to the collection.
		$field->set_value( $items );

		return [
			'success'          => true,
			'message'          => esc_attr__( 'Post removed from collection successfully', 'suretriggers' ),
			'post_id'          => $post_id,
			'collection_id'    => $collection_id,
			'collection_url'   => get_permalink( $collection_id ),
			'collection_items' => wp_json_encode( $items ),
		];
	}

}

RemovePostFromCollection::get_instance();
