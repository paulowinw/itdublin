<?php
/**
 * Reusable Video Block Class
 *
 * @package PrestoPlayer\Blocks
 */

namespace PrestoPlayer\Blocks;

use PrestoPlayer\Models\ReusableVideo;

/**
 * Reusable Video Block
 */
class ReusableVideoBlock {
	/**
	 * Block name
	 *
	 * @var string
	 */
	protected $name = 'reusable-display';

	/**
	 * Register Block
	 *
	 * @return void
	 */
	public function register() {
		register_block_type(
			"presto-player/$this->name",
			array(
				'render_callback' => array( $this, 'html' ),
			)
		);
	}

	/**
	 * Dynamic block output
	 *
	 * @param array $attributes Block attributes.
	 *
	 * @return string
	 */
	public function html( $attributes ) {
		// create reusable video block instance.
		$block = new ReusableVideo( $attributes['id'] );

		// avoid override here, so that inner block id is not replaced.
		unset( $attributes['id'] );

		// render block.
		return $block->renderBlock( $attributes );
	}
}
