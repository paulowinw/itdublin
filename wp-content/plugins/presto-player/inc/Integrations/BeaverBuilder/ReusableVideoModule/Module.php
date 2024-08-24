<?php

namespace PrestoPlayer\Integrations\BeaverBuilder\ReusableVideoModule;

use PrestoPlayer\Services\Scripts;
use PrestoPlayer\Models\ReusableVideo;

class Module extends \FLBuilderModule {


	public function __construct() {
		parent::__construct(
			array(
				'name'            => __( 'Presto Player Media', 'presto-player' ),
				'description'     => __( 'Presto Player Media', 'presto-player' ),
				'group'           => __( 'Presto Player', 'presto-player' ),
				'category'        => __( 'Presto Player', 'presto-player' ),
				'dir'             => PRESTO_PLAYER_BB_DIR . 'ReusableVideoModule/',
				'url'             => PRESTO_PLAYER_BB_URL . 'ReusableVideoModule/',
				'icon'            => 'button.svg',
				'editor_export'   => true, // Defaults to true and can be omitted.
				'enabled'         => true, // Defaults to true and can be omitted.
				'partial_refresh' => false, // Defaults to false and can be omitted.
			)
		);

		$this->slug = 'presto-player';
	}

	/**
	 * Should be overridden by subclasses to enqueue
	 * additional css/js using the add_css and add_js methods.
	 *
	 * @since 1.0
	 * @return void
	 */
	public function enqueue_scripts() {
		// always enqueue the css
		$assets = include trailingslashit( PRESTO_PLAYER_PLUGIN_DIR ) . 'dist/beaver-builder.asset.php';
		$this->add_css( 'surecart/beaver-builder/admin', trailingslashit( PRESTO_PLAYER_PLUGIN_URL ) . 'dist/beaver-builder.css', array(), $assets['version'] );

		// Responsive iframe ui is enabled, enqueue the script for the parent window.
		if ( class_exists( '\FLBuilderUIIFrame' ) && \FLBuilderUIIFrame::is_enabled() ) {
			add_action( 'fl_builder_ui_enqueue_scripts', array( $this, 'enqueue_parent_window_script' ) );
		} else {
			$this->add_js(
				'surecart/beaver-builder/admin',
				trailingslashit( PRESTO_PLAYER_PLUGIN_URL ) . 'dist/beaver-builder.js',
				array_merge( array( 'jquery' ), $assets['dependencies'] ),
				$assets['version'],
				true
			);
		}
	}

	/**
	 * Enqueue script for the parent window.
	 *
	 * @since 2.0.13
	 * @return void
	 */
	public function enqueue_parent_window_script() {
		$assets = include trailingslashit( PRESTO_PLAYER_PLUGIN_DIR ) . 'dist/beaver-builder.asset.php';
		wp_enqueue_script(
			'surecart/beaver-builder/admin',
			trailingslashit( PRESTO_PLAYER_PLUGIN_URL ) . 'dist/beaver-builder.js',
			array_merge( array( 'jquery', 'regenerator-runtime' ), $assets['dependencies'] ),
			$assets['version'],
			true
		);
	}

	public static function getSettings() {
		return array(
			'settings' => array(
				'title'    => __( 'Settings', 'presto-player' ),
				'sections' => array(
					'video_select' => array(
						'title'  => 'Media',
						'fields' => array(
							'video_select_ajax' => array(
								'type'    => 'raw',
								'label'   => __( 'Select Media Hub Item', 'fl-builder' ),
								'content' => self::dynamic_dropdown(),
							),
							'video_id'          => array(
								'type' => 'text',
							),
							'video_name'        => array(
								'type' => 'text',
							),
							'url_override'      => array(
								'type'        => 'text',
								'label'       => 'URL Override',
								'connections' => array( 'url' ),
							),
						),
					),
				),
			),
		);
	}

	public static function dynamic_dropdown() {
		ob_start();
		?>
		<div class="presto-builder--custom-video-controls">
			<div class="fl-builder--category-select" x-data="window.prestoBBDropdown({nonce: '<?php echo wp_create_nonce( 'wp_rest' ); ?>'})" x-init="init">
				<div class="fl-builder--selector-display" x-on:click="open">
					<button class="fl-builder--selector-display-label">
						<span class="fl-builder--group-label"><?php esc_html_e( 'Media', 'presto-player' ); ?></span>
						<span class="fl-builder--current-view-name" x-text="video.name || 'Select media'"></span>
					</button>
				</div>

				<div class="presto-builder--selector-menu">
					<div class="presto-builder--menu" x-show="isOpen()" x-on:click.away="close">
						<input class="presto-builder--dropdown-search" x-ref="searchbox" type="text" x-model="search" placeholder="<?php _e( 'Search Media Hub', 'presto-player' ); ?>" />
						<template x-if="loading">
							<svg width='14px' height='14px' xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid" class="uil-ring">
								<rect x="0" y="0" width="100" height="100" fill="none" class="bk"></rect>
								<circle cx="50" cy="50" r="44" stroke-dasharray="179.69909978533616 96.7610537305656" stroke="#2ea2cc" fill="none" stroke-width="12">
									<animateTransform attributeName="transform" type="rotate" values="0 50 50;180 50 50;360 50 50;" keyTimes="0;0.5;1" dur="1s" repeatCount="indefinite" begin="0s"></animateTransform>
								</circle>
							</svg>
						</template>

						<template x-if="!loading" x-for="item in items" :key="item.ID">
							<button class="presto-builder--menu-item" x-text="item.post_title" x-on:click="setVideo(item)"></button>
						</template>
					</div>

				</div>
				<div class="presto-builder--video-edit-buttons">
					<a href="/wp-admin/post-new.php?post_type=pp_video_block" class="fl-builder-button presto-create-bb-video" target="_blank">
						<?php _e( 'Create', 'presto-player' ); ?>
					</a> &nbsp;
					<template x-if="video.id">
						<a x-bind:href="video.editLink" class="fl-builder-button presto-create-bb-video" target="_blank">
							<?php _e( 'Edit', 'presto-player' ); ?>
						</a>
					</template>
				</div>
			</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Display the block
	 *
	 * @return void
	 */
	public function display() {
		if ( ! $this->settings->video_id ) {
			return;
		}

		// global is the most reliable between page builders
		global $load_presto_js;
		$load_presto_js = true;
		( new Scripts() )->blockAssets(); // enqueue block assets

		$video     = new ReusableVideo( $this->settings->video_id );
		$overrides = array();
		if ( $this->settings->url_override ) {
			$overrides['src'] = $this->settings->url_override;
		}
		$render = $video->renderBlock( $overrides );
		if ( $render ) {
			echo $render;
			return;
		}
		return;
	}
}
