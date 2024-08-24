<?php
/**
 * AddPlace.
 * php version 5.6
 *
 * @category AddPlace
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\GeoDirectory\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use Exception;

/**
 * AddPlace
 *
 * @category AddPlace
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class AddPlace extends AutomateAction {

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
	public $action = 'add_or_update_place';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Add/Update Place', 'suretriggers' ),
			'action'   => $this->action,
			'function' => [ $this, 'action_listener' ],
		];

		return $actions;
	}

	/**
	 * Action listener.
	 *
	 * @param int   $user_id user Id.
	 * @param int   $automation_id automation_id.
	 * @param array $fields fields.
	 * @param array $selected_options selectedOptions.
	 *
	 * @return array
	 * @throws Exception Error.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		$post_data                 = [];
		$post_data['post_url']     = $selected_options['post_url'] ? $selected_options['post_url'] : '';
		$post_data['post_title']   = $selected_options['post_title'] ? $selected_options['post_title'] : '';
		$post_content              = $selected_options['post_content'] ? $selected_options['post_content'] : '';
		$patterns                  = [
			'/<head\b[^>]*>.*?<\/head>/is',
			'/<script\b[^>]*>.*?<\/script>/is',
			'/<style\b[^>]*>.*?<\/style>/is',
		];
		$post_data['post_content'] = preg_replace( $patterns, '', $post_content );
		$post_type                 = 'gd_place';
		$post_data['post_type']    = $post_type;
	
		$post_data['post_status'] = $selected_options['post_status'] ? $selected_options['post_status'] : '';
		$meta_array               = [];
	
		if ( ! empty( $selected_options['post_meta'] ) ) {
			foreach ( $selected_options['post_meta'] as $meta ) {
				$meta_key                = $meta['metaKey'];
				$meta_value              = $meta['metaValue'];
				$meta_array[ $meta_key ] = $meta_value;
			}
			$post_data['meta_input'] = $meta_array;
		}
		if ( isset( $selected_options['post_url'] ) && ! empty( $selected_options['post_url'] ) ) {
			$url         = $selected_options['post_url'];
			$parts       = explode( '/', $url );
			$parts       = array_values( array_filter( $parts ) );
			$slug        = end( $parts );
			$post_exists = get_page_by_path( strval( $slug ), OBJECT, 'gd_place' );
			if ( $post_exists ) {
				$post_data['ID'] = $post_exists->ID;
				wp_update_post( $post_data );
				$post_details = static::gd_update_post( $post_exists->ID, $selected_options, true );
				$post_data    = get_post( $post_exists->ID );
	
				return array_merge( (array) $post_data, $post_details );
			} else {
				throw new Exception( 'The URL entered is incorrect. Please provide the correct URL for the post' );
			}
		}
	
		$post_id   = wp_insert_post( $post_data );
		$post_data = (array) get_post( $post_id );
		if ( ! $post_id ) {
			$this->set_error(
				[
					'post_data' => $post_data,
					'msg'       => __( 'Failed to insert post!', 'suretriggers' ),
				]
			);
			return [];
		}
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
		unset( $gd_post['post_url'] );
		$post_type                = 'gd_place';
		$postarr['post_id']       = $post_id;
		$postarr['post_status']   = 'pending';
		$place_category           = array_column( $gd_post['place_category'], 'value' );
		$postarr['post_category'] = implode( ',', $place_category );
		$place_tag                = array_column( $gd_post['place_tag'], 'value' );

		wp_set_post_terms( $post_id, $place_tag, $post_type . '_tags', $update );
		wp_set_post_terms( $post_id, $place_category, $post_type . 'category', $update );
		$postarr['post_tags'] = implode( ',', $place_tag );
		// Save location info.
		$postarr = self::save_location( $gd_post, $postarr );
	
		// Copy post_title to _search_title.
		if ( isset( $gd_post['post_title'] ) ) {
			$postarr['_search_title'] = $gd_post['post_title'];
		}
	
		$format = array_fill( 0, count( $postarr ), '%s' );
	
		$wpdb->update(
			"{$wpdb->prefix}geodir_gd_place_detail",
			$postarr,
			[ 'post_id' => $post_id ],
			$format
		);
		return $postarr;
	}
	
	/**
	 * Save location info.
	 *
	 * @param array $gd_post post data.
	 * @param array $postarr post array.
	 *
	 * @return array
	 */
	private function save_location( $gd_post, $postarr ) {
		if ( isset( $gd_post['street'] ) ) {
			$postarr['street'] = sanitize_text_field( stripslashes( $gd_post['street'] ) );
		}
		if ( isset( $gd_post['city'] ) ) {
			$postarr['city'] = sanitize_text_field( stripslashes( $gd_post['city'] ) );
		}
		if ( isset( $gd_post['country'] ) ) {
			$postarr['country'] = sanitize_text_field( stripslashes( $gd_post['country'] ) );
		}
		if ( isset( $gd_post['zip'] ) ) {
			$postarr['zip'] = sanitize_text_field( stripslashes( $gd_post['zip'] ) );
		}
		if ( isset( $gd_post['latitude'] ) ) {
			$postarr['latitude'] = sanitize_text_field( stripslashes( $gd_post['latitude'] ) );
		}
		if ( isset( $gd_post['longitude'] ) ) {
			$postarr['longitude'] = sanitize_text_field( stripslashes( $gd_post['longitude'] ) );
		}
		return $postarr;
	}
	


}

AddPlace::get_instance();
