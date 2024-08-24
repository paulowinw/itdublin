<?php

namespace PrestoPlayer\Blocks;

use PrestoPlayer\Attachment;
use PrestoPlayer\Support\Block;
use PrestoPlayer\Models\CurrentUser;

class SelfHostedBlock extends Block {

	/**
	 * Block name
	 *
	 * @var string
	 */
	protected $name = 'self-hosted';

	/**
	 * Translated block title
	 */
	protected $title;

	public function __construct( bool $isPremium = false, $version = 1 ) {
		parent::__construct( $isPremium, $version );
		$this->title = __( 'Self-hosted', 'presto-player' );
	}

	/**
	 * Register the block type.
	 *
	 * @return void
	 */
	public function registerBlockType() {
		register_block_type(
			PRESTO_PLAYER_PLUGIN_DIR . 'src/admin/blocks/blocks/hosted',
			array(
				'render_callback' => array( $this, 'html' ),
			)
		);
	}

	/**
	 * Bail if user cannot access video
	 *
	 * @return void
	 */
	public function middleware( $attributes, $content ) {
		// if private and user cannot access video, don't load
		if ( ! empty( $attributes['visibility'] ) && 'private' === $attributes['visibility'] ) {
			if ( ! CurrentUser::canAccessVideo( $attributes['id'] ) ) {
				return false;
			}
		}

		return parent::middleware( $attributes, $content );
	}

	/**
	 * Add src to video
	 *
	 * @param array $attributes
	 * @return void
	 */
	public function sanitizeAttributes( $attributes, $default_config ) {
		$src = ! empty( $attributes['src'] ) ? $attributes['src'] : '';

		if ( ! empty( $this->isHls( $src ) ) ) {
			wp_enqueue_script( 'hls.js' );
		}

		return array(
			'src' => ! empty( $attributes['attachment_id'] ) ? Attachment::getSrc( $attributes['attachment_id'] ) : $src,
		);
	}

	/**
	 * Override attributes
	 *
	 * @param array $attributes
	 * @return array
	 */
	public function overrideAttributes( $attributes ) {
		$load = $this->middleware( $attributes, '' );

		if ( ! $load ) {
			return array();
		}

		return $attributes;
	}
}
