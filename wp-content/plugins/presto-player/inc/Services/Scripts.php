<?php

namespace PrestoPlayer\Services;

use Error;
use PrestoPlayer\Plugin;
use PrestoPlayer\Models\Block;
use PrestoPlayer\Models\Setting;
use PrestoPlayer\WPackio\Enqueue;


class Scripts {

	/**
	 * Register scripts used throughout the plugin
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'enqueue_block_assets', array( $this, 'registerPrestoComponents' ) );
		add_action( 'init', array( $this, 'registerPrestoComponents' ) );
		add_filter( 'script_loader_tag', array( $this, 'prestoComponentsTag' ), 10, 3 );

		// block assets.
		add_action( 'enqueue_block_editor_assets', array( $this, 'blockEditorAssets' ) );
		add_action( 'enqueue_block_assets', array( $this, 'blockAssets' ) );

		// learndash.
		add_action( 'admin_enqueue_scripts', array( $this, 'learndashAdminScripts' ) );

		// elementor editor scripts
		add_action( 'elementor/frontend/before_enqueue_scripts', array( $this, 'elementorPreviewScripts' ) );
		add_action( 'elementor/frontend/before_enqueue_scripts', array( $this, 'blockAssets' ) );

		// admin pages
		add_action( 'admin_print_scripts-presto-player_page_presto_license', array( $this, 'licenseScripts' ) );
		add_action( 'presto_player_pro_register_license_page', array( $this, 'licenseScripts' ) );

		add_action( 'after_setup_theme', array( $this, 'addAppearanceToolsSupport' ), 99999 );

		// custom template styles
		add_action( 'wp_enqueue_scripts', array( $this, 'presto_player_custom_template_styles' ) );
	}

	/**
	 * Add support for Appearance Tools.
	 *
	 * @return void
	 */
	public function addAppearanceToolsSupport() {
		add_theme_support( 'appearance-tools' );
		add_theme_support( 'border' );
	}

	public function learndashAdminScripts( $hook_suffix ) {
		global $post_type;

		// must be on learndash page
		if ( ! in_array( $post_type, array( 'sfwd-lessons', 'sfwd-topic' ) ) ) {
			return;
		}

		// must be on new post page
		if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ) ) ) {
			return;
		}

		$assets = include trailingslashit( PRESTO_PLAYER_PLUGIN_DIR ) . 'dist/learndash.asset.php';
		wp_enqueue_script(
			'surecart/learndash/admin',
			trailingslashit( PRESTO_PLAYER_PLUGIN_URL ) . 'dist/learndash.js',
			array_merge( array( 'jquery' ), $assets['dependencies'] ),
			$assets['version'],
			true
		);
	}

	/**
	 * Preload components to increase performance
	 */
	public function preloadComponents() {
		/**
		 * Get base file needed for components
		 */
		$file_contents = file_get_contents( PRESTO_PLAYER_PLUGIN_DIR . 'dist/components/web-components/web-components.esm.js' );
		preg_match( '/\.\\/p\-(.*)\.js/', $file_contents, $matches );

		// get entry file
		if ( ! empty( $matches[0] ) ) {
			$file = str_replace( './', '', $matches[0] );
			echo "<link rel='modulepreload' href='" . esc_url_raw( PRESTO_PLAYER_PLUGIN_URL . 'dist/components/web-components/' . $file ) . "' as='script' />\n"; // base
		}

		/**
		 * Get entry files
		 */
		$files = scandir( PRESTO_PLAYER_PLUGIN_DIR . 'dist/components/web-components/' );
		foreach ( $files as $file ) {
			if ( strpos( $file, '.entry.js' ) !== false ) {
				echo "<link rel='modulepreload' href='" . esc_url_raw( PRESTO_PLAYER_PLUGIN_URL . 'dist/components/web-components/' . $file ) . "' as='script' />\n"; // list
			}
		}

		/**
		 * Web components loader
		 */
		echo "<link rel='modulepreload' href='" . esc_url_raw( PRESTO_PLAYER_PLUGIN_URL . 'dist/components/web-components/web-components.esm.js' ) . '?ver=' . filemtime( PRESTO_PLAYER_PLUGIN_DIR . 'dist/components/web-components/web-components.esm.js' ) . "' as='script' />";
	}

	/**
	 * Add a type="module" to our components tag to lazy load them
	 */
	public function prestoComponentsTag( $tag, $handle, $source ) {
		if ( 'presto-components' === $handle ) {
			$tag = '<script src="' . $source . '" type="module" defer></script>';
		}

		return $tag;
	}

	/**
	 * Register our components
	 */
	public function registerPrestoComponents() {

		$file = is_admin() || ! Setting::get( 'performance', 'module_enabled' ) ? 'src/player/player-static.js' : 'dist/components/web-components/web-components.esm.js';

		wp_register_script(
			'hls.js',
			PRESTO_PLAYER_PLUGIN_URL . 'src/libraries/hls.min.js',
			array(),
			'1.4.8',
			true
		);

		$deps = array(
			'jquery',
			'wp-hooks',
			'wp-i18n',
		);

		if ( is_admin() ) {
			$deps[] = 'hls.js';
		}

		wp_register_script(
			'presto-components',
			PRESTO_PLAYER_PLUGIN_URL . $file,
			$deps,
			filemtime( PRESTO_PLAYER_PLUGIN_DIR . $file ),
			true
		);

		wp_localize_script(
			'presto-components',
			'prestoComponents',
			array(
				'url' => PRESTO_PLAYER_PLUGIN_URL . 'dist/components/web-components/web-components.esm.js?ver=' . filemtime( PRESTO_PLAYER_PLUGIN_DIR . 'dist/components/web-components/web-components.esm.js' ),
			)
		);

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( 'presto-components', 'presto-player' );
		}

		wp_localize_script(
			'presto-components',
			'prestoPlayer',
			apply_filters(
				'presto-settings-block-js-options',
				array(
					'plugin_url'          => esc_url_raw( trailingslashit( PRESTO_PLAYER_PLUGIN_URL ) ),
					'logged_in'           => is_user_logged_in(),
					'root'                => esc_url_raw( get_rest_url() ),
					'nonce'               => wp_create_nonce( 'wp_rest' ),
					'ajaxurl'             => admin_url( 'admin-ajax.php' ),
					'isAdmin'             => is_admin(),
					'isSetup'             => array(
						'bunny' => false,
					),
					'proVersion'          => Plugin::proVersion(),
					'isPremium'           => Plugin::isPro(),
					'wpVersionString'     => 'wp/v2/',
					'prestoVersionString' => 'presto-player/v1/',
					'debug'               => defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG,
					'debug_navigator'     => defined( 'PRESTO_DEBUG_NAVIGATOR' ) && PRESTO_DEBUG_NAVIGATOR,
					'i18n'                => Translation::geti18n(),
				)
			)
		);
	}

	/**
	 * Elementor scripts (needed speifically on preview pages)
	 */
	public function elementorPreviewScripts() {
		if ( ! isset( $_GET['elementor-preview'] ) ) {
			return;
		}

		$assets = include trailingslashit( PRESTO_PLAYER_PLUGIN_DIR ) . 'dist/elementor.asset.php';
		wp_enqueue_script(
			'surecart/elementor',
			trailingslashit( PRESTO_PLAYER_PLUGIN_URL ) . 'dist/elementor.js',
			array_merge( array( 'jquery', 'hls.js' ), $assets['dependencies'] ),
			$assets['version'],
			true
		);

		wp_localize_script(
			'surecart/elementor',
			'prestoEditorData',
			array(
				'proVersion'      => Plugin::proVersion(),
				'isPremium'       => Plugin::isPro(),
				'root'            => esc_url_raw( get_rest_url() ),
				'nonce'           => wp_create_nonce( 'wp_rest' ),
				'wpVersionString' => 'wp/v2/',
				'siteURL'         => esc_url_raw( untrailingslashit( get_site_url( get_current_blog_id() ) ) ),
			)
		);
	}

	/**
	 * Block Editor Assets
	 *
	 * @return void
	 */
	public function blockEditorAssets() {
		if ( ! is_admin() ) {
			return;
		}

		$assets = include trailingslashit( PRESTO_PLAYER_PLUGIN_DIR ) . 'dist/blocks.asset.php';
		wp_enqueue_script(
			'surecart/blocks/admin',
			trailingslashit( PRESTO_PLAYER_PLUGIN_URL ) . 'dist/blocks.js',
			array_merge( array( 'presto-components', 'hls.js', 'regenerator-runtime' ), $assets['dependencies'] ),
			$assets['version'],
			true
		);
		wp_enqueue_style( 'surecart/blocks/admin', trailingslashit( PRESTO_PLAYER_PLUGIN_URL ) . 'dist/blocks.css', array(), $assets['version'] );

		wp_localize_script(
			'surecart/blocks/admin',
			'prestoPlayer',
			apply_filters(
				'presto_player_admin_script_options',
				array(
					'plugin_url'          => esc_url_raw( trailingslashit( PRESTO_PLAYER_PLUGIN_URL ) ),
					'root'                => esc_url_raw( get_rest_url() ),
					'nonce'               => wp_create_nonce( 'wp_rest' ),
					'ajaxurl'             => admin_url( 'admin-ajax.php' ),
					'isAdmin'             => is_admin(),
					'proVersion'          => Plugin::proVersion(),
					'isPremium'           => Plugin::isPro(),
					'isSetup'             => array(
						'bunny' => false,
					),
					'wpVersionString'     => 'wp/v2/',
					'prestoVersionString' => 'presto-player/v1/',
					'defaults'            => array(
						'color' => Setting::getDefaultColor(),
					),
					'i18n'                => Translation::geti18n(),
				)
			)
		);

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( 'surecart/blocks/admin', 'presto-player' );
		}

		wp_localize_script( 'surecart/blocks/admin', 'scIcons', array( 'path' => esc_url_raw( plugin_dir_url( PRESTO_PLAYER_PLUGIN_FILE ) . 'dist/icon-assets' ) ) );

		wp_localize_script(
			'surecart/blocks/admin',
			'prestoPlayerAdmin',
			apply_filters(
				'presto_player_admin_block_script_options',
				array(
					'root'                => esc_url_raw( get_rest_url() ),
					'nonce'               => wp_create_nonce( 'wp_rest' ),
					'logged_in'           => is_user_logged_in(),
					'ajaxurl'             => admin_url( 'admin-ajax.php' ),
					'wp_max_upload_size'  => wp_max_upload_size(),
					'isAdmin'             => is_admin(),
					'proVersion'          => Plugin::proVersion(),
					'isPremium'           => Plugin::isPro(),
					'isSetup'             => array(
						'bunny' => false,
					),
					'wpVersionString'     => 'wp/v2/',
					'prestoVersionString' => 'presto-player/v1/',
					'defaults'            => array(
						'color' => Setting::getDefaultColor(),
					),
				)
			)
		);
	}

	/**
	 * Does the page have a player?
	 */
	public function hasPlayer() {
		// global is the most reliable between page builders
		global $load_presto_js;
		if ( $load_presto_js ) {
			return true;
		}

		// must be a singular page
		if ( ! is_singular() ) {
			return false;
		}

		$id            = get_the_ID();
		$widget_blocks = get_option( 'widget_block' );

		// change to see if we have one of our blocks
		$types = Block::getBlockTypes();
		foreach ( $types as $type ) {
			if ( has_block( $type, $id ) ) {
				return true;
			}

			if ( ! empty( $widget_blocks ) ) {
				foreach ( $widget_blocks as $block ) {
					$content = isset( $block['content'] ) ? $block['content'] : '';
					if ( ! empty( $content ) && has_block( $type, $content ) ) {
						return true;
					}
				}
			}
		}

		// check for data-presto-config (player rendered)
		$wp_post = get_post( $id );
		if ( $wp_post instanceof \WP_Post ) {
			$post = $wp_post->post_content;
		}
		$has_player = false !== strpos( $post, 'data-presto-config' );
		if ( $has_player ) {
			return true;
		}

		// check that we have a shortcode
		if ( has_shortcode( $post, 'presto_player' ) ) {
			return true;
		}

		// enable on Elementor
		if ( ! empty( $_GET['action'] ) && 'elementor' === $_GET['action'] ) {
			return true;
		}
		if ( isset( $_GET['elementor-preview'] ) ) {
			return true;
		}

		// load for beaver builder
		if ( isset( $_GET['fl_builder'] ) ) {
			return true;
		}

		// tutor LMS
		global $post;
		if ( ! empty( $post->post_type ) && $post->post_type ) {
			if ( defined( 'TUTOR_VERSION' ) && 'lesson' === $post->post_type ) {
				return true;
			}
		}

		// load for Divi builder
		if ( isset( $_GET['et_fb'] ) ) {
			return true;
		}

		// do we have the player
		return $has_player;
	}

	/**
	 * Add global player styles inline.
	 *
	 * @return void
	 */
	public function globalStyles() {
		?>
		<style>
			<?php readfile( PRESTO_PLAYER_PLUGIN_DIR . 'src/player/global.css' ); ?>
		</style>
		<?php
	}

	public function loadJavascript() {
		// global styles
		if ( ! wp_doing_ajax() && ! defined( 'REST_REQUEST' ) && ! defined( 'PRESTO_TESTSUITE' ) ) {
			$this->globalStyles();
		}

		// direct load
		if ( Setting::get( 'performance', 'module_enabled' ) || ! is_admin() ) {
			// preload components
			add_action( 'wp_head', array( $this, 'preloadComponents' ) );
		}

		wp_enqueue_script( 'presto-components' );
	}

	/**
	 * Block frontend assets
	 *
	 * @return void
	 */
	public function blockAssets() {
		// don't output if it doesn't have our block
		if ( ! apply_filters( 'presto_player_load_js', $this->hasPlayer() ) ) {
			return;
		}

		$this->loadJavascript();

		// fallback styles and script to load iframes
		add_action(
			'wp_footer',
			function () {
				if ( is_admin() ) {
					return;
				}
				if ( ! apply_filters( 'presto_player/scripts/load_iframe_fallback', false ) ) {
					return;
				}
				$this->printFallbackScriptsAndStyles();
			}
		);
	}

	public function licenseScripts( $hook ) {
		add_action(
			"admin_print_scripts-{$hook}",
			function () {
				$assets = include trailingslashit( PRESTO_PLAYER_PLUGIN_DIR ) . 'dist/license.asset.php';
				wp_enqueue_script(
					'surecart/license/admin',
					trailingslashit( PRESTO_PLAYER_PLUGIN_URL ) . 'dist/license.js',
					array_merge( $assets['dependencies'] ),
					$assets['version'],
					true
				);
				wp_enqueue_style( 'surecart/license/admin', trailingslashit( PRESTO_PLAYER_PLUGIN_URL ) . 'dist/license.css', array(), $assets['version'] );
			}
		);
	}

	public function printFallbackScriptsAndStyles() {
		/*
		* This CSS is duplicated in 'packages/components/src/components/core/player/presto-player/presto-player.scss'
		*/
		echo '<style>.presto-iframe-fallback-container{position:relative;padding-bottom:56.25%;padding-top:30px;height:0;overflow:hidden}.presto-iframe-fallback-container embed,.presto-iframe-fallback-container iframe,.presto-iframe-fallback-container object{position:absolute;top:0;left:0;width:100%;height:100%}</style>';
		echo '<script defer>
                window.addEventListener("load", function(event) {
                    setTimeout(function() {
                        var deferVideo = document.getElementsByClassName("presto-fallback-iframe");
                        if (!deferVideo.length) return;
                        Array.from(deferVideo).forEach(function(video) {
                            video && video.setAttribute("src", video.getAttribute("data-src"));
                        });
                    }, 2000);
                }, false);
            </script>';
	}

	public function presto_player_custom_template_styles() {
		if ( is_singular( 'pp_video_block' ) ) {
			$assets = include trailingslashit( PRESTO_PLAYER_PLUGIN_DIR ) . 'dist/media-page.asset.php';
			wp_enqueue_style(
				'presto-player/media-page',
				trailingslashit( PRESTO_PLAYER_PLUGIN_URL ) . 'dist/media-page.css',
				array(),
				$assets['version']
			);
		}
	}
}
