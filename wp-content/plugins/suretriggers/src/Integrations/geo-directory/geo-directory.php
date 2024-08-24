<?php
/**
 * GeoDirectory core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\GeoDirectory;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;
use WP_Term;

/**
 * Class GeoDirectory
 *
 * @package SureTriggers\Integrations\GeoDirectory
 */
class GeoDirectory extends Integrations {


	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'GeoDirectory';

	/**
	 * Get term details
	 *
	 * @param array  $gd_tags gd tags.
	 * @param string $taxonomy taxonomy.
	 * @return array
	 */
	public static function get_place_terms( $gd_tags, $taxonomy ) {
		$terms = [];
		foreach ( $gd_tags as $tag ) {
			$term = get_term_by( 'name', $tag, $taxonomy );
			if ( $term instanceof WP_Term ) {
				$term_id = $term->term_id;
			} else {
				$term = get_term_by( 'slug', $tag, $taxonomy );
				if ( $term instanceof WP_Term ) {
					$term_id = $term->term_id;
				} else {
					$term = get_term_by( 'id', $tag, $taxonomy );
					if ( $term instanceof WP_Term ) {
						$term_id = $term->term_id;
					} else {
						// If term is not found, set term_id to null or handle appropriately.
						$term_id = null;
					}
				}
			}
			// Only push term_id if it's not null.
			if ( null !== $term_id ) {
				$terms[] = $term_id;
			}
		}
		return $terms;
	}
	
	

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( self::class );
	}

}

IntegrationsController::register( GeoDirectory::class );
