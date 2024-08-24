<?php

namespace PrestoPlayer\Blocks;

use PrestoPlayer\Attachment;
use PrestoPlayer\Models\CurrentUser;
use PrestoPlayer\Support\Block;

class AudioBlock extends Block {

	/**
	 * Block name
	 *
	 * @var string
	 */
	protected $name = 'audio';

	/**
	 * Translated block title
	 */
	protected $title;

	public function __construct( bool $isPremium = false, $version = 1 ) {
		parent::__construct( $isPremium, $version );
		$this->title = __( 'Audio', 'presto-player' );
	}

	/**
	 * Bail if user cannot access video
	 *
	 * @return void
	 */
	public function middleware( $attributes, $content ) {
		// if private and user cannot access video, don't load
		if ( ! empty( $attributes['visibility'] ) && 'private' === $attributes['visibility'] ) {
			if ( empty( $attributes['id'] ) ) {
				return false;
			}
			if ( ! CurrentUser::canAccessVideo( $attributes['id'] ) ) {
				return false;
			}
		}

		return parent::middleware( $attributes, $content );
	}

	/**
	 * Add curtain styles.
	 *
	 * @return void
	 */
	public function sanitizeAttributes( $attributes, $default_config ) {

		$src = ! empty( $attributes['src'] ) ? $attributes['src'] : '';

		return array(
			'src'    => ! empty( $attributes['attachment_id'] ) ? Attachment::getSrc( $attributes['attachment_id'] ) : $src,
			'styles' => $default_config['styles'] . ' --presto-curtain-size: 25%',
		);
	}

	/**
	 * Register the block type.
	 *
	 * @return void
	 */
	public function registerBlockType() {
		register_block_type(
			PRESTO_PLAYER_PLUGIN_DIR . 'src/admin/blocks/blocks/audio',
			array(
				'render_callback' => array( $this, 'html' ),
			)
		);
	}
}
