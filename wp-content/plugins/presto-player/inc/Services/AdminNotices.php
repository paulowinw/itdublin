<?php

namespace PrestoPlayer\Services;

use Astra_Notices;
use PrestoPlayer\Models\Video;

class AdminNotices {

	public function register() {
		add_action( 'admin_init', array( $this, 'dismiss' ) );
		$this->displayRatingsNotice();
	}

	public function displayRatingsNotice() {
		require_once PRESTO_PLAYER_PLUGIN_DIR . 'vendor/brainstormforce/astra-notices/class-astra-notices.php';
		$image_path = PRESTO_PLAYER_PLUGIN_URL . 'img/presto-player-icon-color.png';

		Astra_Notices::add_notice(
			array(
				'id'                         => 'presto-player-rating',
				'type'                       => '',
				'message'                    => sprintf(
					'<div class="notice-image">
						<img src="%1$s" class="custom-logo" alt="Sidebar Manager" itemprop="logo"></div> 
						<div class="notice-content">
							<div class="notice-heading">
								%2$s
							</div>
							%3$s<br />
							<div class="astra-review-notice-container">
								<a href="%4$s" class="astra-notice-close astra-review-notice button-primary" target="_blank">
								%5$s
								</a>
							<span class="dashicons dashicons-calendar"></span>
								<a href="#" data-repeat-notice-after="%6$s" class="astra-notice-close astra-review-notice">
								%7$s
								</a>
							<span class="dashicons dashicons-smiley"></span>
								<a href="#" class="astra-notice-close astra-review-notice">
								%8$s
								</a>
							</div>
						</div>',
					$image_path,
					__( 'Thanks a ton for choosing Presto Player! We are hard at work adding more features to help you harness the power of videos.', 'presto-player' ),
					__( 'Could you please do us a BIG favor and give us a 5-star rating on WordPress? It really boosts the motivation of our team.', 'presto-player' ),
					'https://wordpress.org/support/plugin/presto-player/reviews/?filter=5#new-post',
					__( 'Ok, you deserve it', 'presto-player' ),
					MONTH_IN_SECONDS,
					__( 'Nope, maybe later', 'presto-player' ),
					__( 'I already did', 'presto-player' )
				),
				'show_if'                    => $this->maybeDisplayRatingsNotice(),
				'repeat-notice-after'        => MONTH_IN_SECONDS,
				'display-notice-after'       => 604800, // Display notice after 7 days.
				'priority'                   => 18,
				'display-with-other-notices' => false,
			)
		);
	}

	/*
	 * Check whether to display notice or not.
	*/
	public function maybeDisplayRatingsNotice() {
		$transient_status = get_transient( 'presto-player-rating' );

		if ( false !== $transient_status ) {
			return false;
		}

		$video_count = $this->getVideosCount();
		// Display ratings notice if video count is more than 1.
		return 0 < $video_count ? true : false;
	}

	public function getVideosCount() {
		$video = new Video();
		$items = $video->fetch(
			array(
				'per_page' => 1,
			)
		);

		return $items->total;
	}

	public static function isDismissed( $name ) {
		return (bool) get_option( 'presto_player_dismissed_notice_' . sanitize_text_field( $name ), false );
	}

	public function dismiss() {
		// permissions check
		if ( ! current_user_can( 'install_plugins' ) ) {
			return;
		}

		// not our notices, bail
		if ( ! isset( $_GET['presto_action'] ) || 'dismiss_notices' !== $_GET['presto_action'] ) {
			return;
		}

		// get notice
		$notice = ! empty( $_GET['presto_notice'] ) ? sanitize_text_field( $_GET['presto_notice'] ) : '';
		if ( ! $notice ) {
			return;
		}

		// notice is dismissed
		update_option( 'presto_player_dismissed_notice_' . sanitize_text_field( $notice ), 1 );
	}
}
