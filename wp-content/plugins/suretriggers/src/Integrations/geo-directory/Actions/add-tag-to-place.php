<?php
/**
 * AddTagToPlace.
 * php version 5.6
 *
 * @category AddTagToPlace
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\GeoDirectory\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\GeoDirectory\GeoDirectory;
use Exception;

/**
 * AddTagToPlace
 *
 * @category AddTagToPlace
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class AddTagToPlace extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'GeoDirectory';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'add_tag_to_place';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Add Tag To Place', 'suretriggers' ),
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
	 * @throws Exception Error.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		$post_data = [];
		$post_id   = $selected_options['post'] ? $selected_options['post'] : 0;
		
		$post_details = static::gd_update_post( $post_id, $selected_options, false );
	
		$post_data = (array) get_post( $post_id );
		return array_merge( $post_data, $post_details );
	}
	
	/**
	 * Update Place Table
	 *
	 * @param int   $post_id post_id.
	 * @param array $gd_post post data.
	 * @param bool  $update New or update.
	 *
	 * @return array
	 * @throws Exception Error.
	 */
	public function gd_update_post( $post_id, $gd_post, $update ) {
		global $wpdb;
		$tags['tags']  = [];
		$post_type     = 'gd_place';
		$taxonomy      = $post_type . '_tags';
		$place_tag     = explode( ',', $gd_post['place_tag'] );
		$place_tag_ids = GeoDirectory::get_place_terms( $place_tag, $taxonomy );
		wp_set_post_terms( $post_id, $place_tag_ids, $taxonomy, true );
		$placetags = wp_get_post_terms( $post_id, $taxonomy, [ 'fields' => 'names' ] );

		if ( ! empty( $placetags ) && ! is_wp_error( $placetags ) ) {
			$tags['tags'] = $placetags;
		}
		return $tags;
	}

}

AddTagToPlace::get_instance();
