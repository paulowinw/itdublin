<?php

namespace PrestoPlayer\Services;

use PrestoPlayer\Services\Scripts;


class ReusableVideos {

	/**
	 * Register shortcode
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'admin_notices', array( $this, 'notice' ) );
		add_action( 'admin_init', array( $this, 'dismissNotice' ) );
	}

	public function notice() {
		global $typenow, $current_screen;

		if ( ! ( $typenow === 'pp_video_block' && $current_screen->base === 'edit' ) ) {
			return;
		}

		$notice_name = 'presto_player_reusable_notice';
		if ( AdminNotices::isDismissed( $notice_name ) ) {
			return;
		}
		?>
		<div class="notice" style="border-left-color: #7c3aed;">
			<p><strong><?php _e( 'What is the media hub?', 'presto-player' ); ?></strong></p>
			<p><?php _e( 'The media hub is a more flexible way to add media to your site. It allows you to save audio and videos which you can later use in any post or page on your site - either through the Block Editor, a page builder, or by using a shortcode or php function.', 'presto-player' ); ?></p>
			<p><a href="
			<?php
			echo esc_url(
				add_query_arg(
					array(
						'presto_action' => 'dismiss_notices',
						'presto_notice' => $notice_name,
					)
				)
			);
			?>
						"><?php _e( 'Dismiss Notice', 'presto-player' ); ?></a></p>
		</div>
		<?php
	}

	public function dismissNotice() {
		// permissions check
		if ( ! current_user_can( 'update_options' ) ) {
			return;
		}

		// not our notices, bail
		if ( ! isset( $_GET['presto_action'] ) || 'dismiss_notice' !== $_GET['presto_action'] ) {
			return;
		}

		$notice = ! empty( $_GET['presto_notice'] ) ? sanitize_text_field( $_GET['presto_notice'] ) : '';

		if ( ! $notice ) {
			return;
		}

		// notice is dismissed
		update_option( 'presto_player_dismissed_notice_' . sanitize_text_field( $notice ), 1 );
	}

	/**
	 * Get reusable video block function.
	 *
	 * @param mixed $id The ID of the reusable block.
	 * @return $content The content of the block.
	 */
	public static function get( $id ) {
		$content_post = get_post( $id );
		$content      = $content_post->post_content;
		return $content;
	}

	public static function getBlock( $id ) {
		$blocks = parse_blocks( self::get( $id ) );
		$out    = '';
		if ( is_feed() ) {
			foreach ( $blocks as $block ) {
				foreach ( $block['innerBlocks'] as $innerblock ) {
					$out .= self::getFeedHtml( $innerblock );
				}
			}
			return $out;
		}

		foreach ( $blocks as $block ) {
			$out .= render_block( $block );
		}

		return $out;
	}

	/**
	 * Display block function.
	 *
	 * @param mixed $id The ID of the reusable block.
	 */
	public static function display( $id ) {
		echo self::getBlock( $id );
	}

	/**
	 * Return fallback html for feeds.
	 *
	 * @param array $block array of block attributes.
	 */
	public static function getFeedHtml( $block ) {

		$html = '';
		if ( $block['blockName'] === 'presto-player/vimeo' ) {
			ob_start();
			?>
			<div class="presto-iframe-fallback-container">
				<iframe style="width: 100%" class="presto-fallback-iframe" id="presto-iframe-fallback-<?php echo (int) $block['attrs']['id']; ?>" src="<?php echo esc_attr( $block['attrs']['src'] ); ?>"></iframe>
			</div>
			<?php
			$html = ob_get_clean();
		} elseif ( $block['blockName'] === 'presto-player/youtube' ) {
			ob_start();
			?>
			<div class="presto-iframe-fallback-container">
				<iframe style="width: 100%" class="presto-fallback-iframe" id="presto-iframe-fallback-<?php echo (int) $block['attrs']['id']; ?>" src="<?php echo esc_attr( $block['attrs']['src'] ); ?>"></iframe>
			</div>
			<?php
			$html = ob_get_clean();
		} else {
			ob_start();
			?>
			<video controls preload="none">
				<source src="<?php echo esc_attr( $block['attrs']['src'] ); ?>" />
			</video>
			<?php
			$html = ob_get_clean();
		}
		return $html;
	}
}
