<?php

namespace PrestoPlayer;

use PrestoPlayer\Services\AdminNotices;
use PrestoPlayer\Services\Streamer;

class Attachment {

	protected $is_premium;

	public function __construct( $is_premium = false ) {
		$this->is_premium = $is_premium;
	}

	public function register() {
		if ( $this->is_premium ) {
			add_action( 'admin_notices', array( $this, 'checkServer' ) );
		}
		add_action( 'wp_get_attachment_url', array( $this, 'replaceLink' ), 10, 2 );
		add_action( 'query_vars', array( $this, 'addQueryVars' ) );
		add_action( 'generate_rewrite_rules', array( $this, 'customRewriteRules' ) );
		add_action( 'template_redirect', array( $this, 'loadVirtualPage' ) );
		add_action( 'wp_ajax_presto_player_load_user_video', array( $this, 'refreshAjaxTempSecurityUser' ) );

		return $this;
	}

	public function refreshAjaxTempSecurityUser( $action ) {
		if ( empty( $_POST['type'] ) ) {
			wp_send_json_error( 'type not set' );
		}

		if ( ! defined( 'DOING_AJAX' ) && ! is_user_logged_in() ) {
			wp_redirect( home_url() );
			exit();
		}

		check_ajax_referer( 'presto_player' );

		if ( $_POST['type'] === 'private-hosted' ) {
			if ( isset( $_POST['id'] ) ) {
				$post_id = (int) $_POST['id'];
				$this->setVideoTransient( (int) $post_id );
				wp_send_json_success( $this->getSrc( (int) $post_id, true ) );
			}
		}

		if ( ! $this->is_premium ) {
			wp_send_json_success();
			return;
		}

		wp_send_json_success();
	}

	public function getTransientKey() {
		if ( ! function_exists( 'wp_get_current_user' ) ) {
			return '';
		}
		$current_user = \wp_get_current_user();
		return 'presto-player-user-' . $current_user->ID;
	}

	/**
	 * Adds query vars for rewrites
	 *
	 * @param array $query_vars
	 * @return array
	 */
	public function addQueryVars( $query_vars ) {
		$query_vars[] = 'presto-player-video';
		$query_vars[] = 'presto-player-token';
		return $query_vars;
	}

	/**
	 * Add custom rewrite rules
	 *
	 * @param \WP_Rewrite $wp_rewrite
	 * @return void
	 */
	public function customRewriteRules( $wp_rewrite ) {
		$wp_rewrite->rules = array_merge(
			array( 'video-src/([^/]*)/(\d+)/?$' => 'index.php?presto-player-token=$matches[1]&presto-player-video=$matches[2]' ),
			$wp_rewrite->rules
		);
	}

	/**
	 * Load virtual template to stream video by id
	 */
	public function loadVirtualPage() {
		// get video attachment id
		$video_id = intval( get_query_var( 'presto-player-video' ) );
		// get the token
		$token = sanitize_text_field( get_query_var( 'presto-player-token' ) );

		if ( $video_id && $token ) {
			if ( ! is_user_logged_in() ) {
				wp_die( 'Access denied! :(', 'Access Denied', array( 'response' => 403 ) );
			}
			$this->checkAndLoadStream( wp_get_current_user(), $video_id, $token );
			die;
		}
	}

	/**
	 * Check the server
	 *
	 * @return void
	 */
	public function checkServer() {
		// check for nginx
		$notice_name     = 'nginx_rules';
		$server_software = isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : false;
		if ( ! stristr( $server_software, 'nginx' ) ) {
			return;
		}

		if ( current_user_can( 'install_plugins' ) && ! AdminNotices::isDismissed( $notice_name ) ) {
			$this->showNotice( $notice_name );
		}
	}

	public function showNotice( $notice_name ) {
		ob_start(); ?>

		<div class="error">
			<h3>Presto Player</h3>
			<p><?php printf( __( 'The video files in the %s folder are not currently protected due to your site running on NGINX.', 'presto-player' ), '<strong>presto-player-private</strong>' ); ?></p>
			<p><?php _e( 'If you plan on using private video, you will want to protect this directory. To protect them, you must add a firewall rule as explained in <a href="https://prestoplayer.com/protecting-videos-with-nginx" target="_blank">this guide</a>.', 'presto-player' ); ?></p>
			<p><?php _e( 'If you have already added the rule, you may safely dismiss this notice', 'presto-player' ); ?></p>
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
		echo ob_get_clean();
	}

	/**
	 * Sets the transient for video access
	 * Sets this for 24 hours
	 *
	 * @param integer $post_id
	 * @return void
	 */
	public function setVideoTransient( $post_id ) {
		$videos   = (array) get_transient( $this->getTransientKey() );
		$videos[] = sanitize_text_field( $post_id );

		// set temporary user transient for access for 1 hour
		set_transient( $this->getTransientKey(), array_filter( array_unique( $videos ) ), 24 * HOUR_IN_SECONDS );
	}

	public static function getSrc( $id, $private = false ) {
		if ( $private ) {
			return self::getPrivateSrc( $id );
		}
		return wp_get_attachment_url( $id );
	}

	public static function getPublicSrc( $id ) {
		global $presto_override_private_url;
		$old                         = $presto_override_private_url;
		$presto_override_private_url = true;
		$url                         = wp_get_attachment_url( $id );
		$presto_override_private_url = $old;
		return $url;
	}

	public static function isPrivate( $id ) {
		return strpos( wp_get_attachment_url( $id ), 'video-src' );
	}

	public static function getPrivateSrc( $id ) {
		if ( ! function_exists( 'wp_create_nonce' ) ) {
			return '';
		}
		// set temporary user transient for access for 1 hour
		( new self() )->setVideoTransient( $id );
		if ( ! get_option( 'permalink_structure' ) ) {
			return sprintf( site_url( '?presto-player-video=%d&presto-player-token=%s' ), $id, wp_create_nonce( 'presto-player-user-token' ) );
		}
		return sprintf( site_url( 'video-src/%s/%d' ), wp_create_nonce( 'presto-player-user-token' ), $id );
	}

	/**
	 * Replaces attachment link
	 *
	 * @param [type] $url
	 * @param [type] $post_id
	 * @return void
	 */
	public function replaceLink( $url, $post_id ) {
		global $presto_override_private_url;

		// only replace for our folder
		if ( ! stristr( $url, 'presto-player-private' ) ) {
			return $url;
		}

		if ( ! $presto_override_private_url ) {
			return self::getPrivateSrc( $post_id );
		} else {
			return $url;
		}
	}

	/**
	 * Check and load stream through PHP
	 *
	 * @param \WP_User $current_user
	 * @param integer  $attachment_id
	 * @param string   $token
	 * @return void
	 */
	public function checkAndLoadStream( $current_user, $attachment_id, $token ) {
		$security_token     = isset( $token ) ? wp_verify_nonce( $token, 'presto-player-user-token' ) : false;
		$temp_security_user = get_transient( $this->getTransientKey() );

		/**
		 * Start video stream with the correct video SRC only in case of pass security rules
		 */
		if ( $security_token && $temp_security_user && $attachment_id > 0 && in_array( $attachment_id, $temp_security_user ) ) {
			$video_file = get_attached_file( $attachment_id );
			$file_type  = wp_check_filetype( $video_file );

			/**
			 * Start video stream to show the video
			 */
			$video_stream = new Streamer( $video_file, $file_type['type'] );
			$video_stream->start();
			exit();
		} else {

			/**
			 * Alert user about the misconduct by accessing directly
			 */
			$message = sprintf(
				__( 'Sorry %1$s! Access to this video is not allowed. An administrator will be informed.', 'presto-player' ),
				ucfirst( $current_user->display_name )
			);
			wp_die( $message, __( 'Forbidden', 'presto-player' ), 403 );
		}
	}
}
