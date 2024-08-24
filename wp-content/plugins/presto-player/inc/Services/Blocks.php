<?php

namespace PrestoPlayer\Services;

/**
 * Registers all blocks
 */
class Blocks {

	/**
	 * Register blocks
	 *
	 * @return Blocks
	 */
	public function register() {
		global $wp_version;
		if ( version_compare( $wp_version, '5.8', '>=' ) ) {
			add_filter( 'block_categories_all', array( $this, 'category' ), 10, 2 );
		} else {
			add_filter( 'block_categories', array( $this, 'categoryDeprecated' ) );
		}

		return $this;
	}

	/**
	 * Give the blocks a category
	 *
	 * @param array $categories
	 * @return array
	 */
	public function category( $block_categories, $editor_context ) {
		if ( ! empty( $editor_context->post ) ) {
			array_push(
				$block_categories,
				array(
					'slug'  => 'presto',
					'title' => __( 'Presto', 'presto-player' ),
					'icon'  => null,
				)
			);
		}

		return $block_categories;
	}

	/**
	 * Give the blocks a category
	 *
	 * @param array $categories
	 * @return array
	 */
	public function categoryDeprecated( $categories ) {
		return array_merge(
			array(
				array(
					'slug'  => 'presto',
					'title' => __( 'Presto', 'presto-player' ),
				),
			),
			$categories
		);
	}
}
