<?php

namespace PrestoPlayer\Blocks;

use PrestoPlayer\Support\Block;

class VimeoBlock extends Block {

	/**
	 * Block name
	 *
	 * @var string
	 */
	protected $name = 'vimeo';

	/**
	 * Translated block title
	 */
	protected $title;

	public function __construct( bool $isPremium = false, $version = 1 ) {
		parent::__construct( $isPremium, $version );
		$this->title = __( 'Vimeo', 'presto-player' );
	}

	/**
	 * Register the block type.
	 *
	 * @return void
	 */
	public function registerBlockType() {
		register_block_type(
			PRESTO_PLAYER_PLUGIN_DIR . 'src/admin/blocks/blocks/vimeo',
			array(
				'render_callback' => array( $this, 'html' ),
			)
		);
	}
}
