<?php
/**
 * SetCollectionPostVerified.
 * php version 5.6
 *
 * @category SetCollectionPostVerified
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
 * SetCollectionPostVerified
 *
 * @category SetCollectionPostVerified
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class SetCollectionPostVerified extends AutomateAction {

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
	public $action = 'voxel_set_collection_post_verified';

	use SingletonLoader;

	/**
	 * Register action.
	 *
	 * @param array $actions action data.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Set Collection Post Verified', 'suretriggers' ),
			'action'   => 'voxel_set_collection_post_verified',
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

		if ( ! class_exists( 'Voxel\Post' ) ) {
			return false;
		}

		$post = \Voxel\Post::force_get( $post_id );

		if ( ! $post ) {
			throw new Exception( 'Post not found' );
		}

		// Set the post as verified.
		$post->set_verified( true );

		return [
			'success' => true,
			'message' => esc_attr__( 'Post Set as Verified', 'suretriggers' ),
		];
	}

}

SetCollectionPostVerified::get_instance();
