<?php

namespace PrestoPlayer\Services;

use PrestoPlayer\Contracts\Service;

class Player implements Service {

	public function register() {
		// ajax percentage actions
		add_action( 'wp_ajax_presto_player_progress_percent', array( $this, 'progressAjaxPercent' ) );
		add_action( 'wp_ajax_nopriv_presto_player_progress_percent', array( $this, 'progressAjaxPercent' ) );

		add_action( 'wp_ajax_nopriv_presto_refresh_progress_nonce', array( $this, 'generateNonce' ) );
		add_action( 'wp_ajax_presto_refresh_progress_nonce', array( $this, 'generateNonce' ) );
	}

	// refresh nonce
	public function generateNonce() {
		return wp_send_json_success( wp_create_nonce( 'wp_rest' ) );
	}

	/**
	 * Run ajax percent action
	 *
	 * @return void
	 */
	public function progressAjaxPercent() {
		$response = $this->progressAction();
		if ( is_wp_error( $response ) ) {
			wp_send_json_error( $response->get_error_message(), $response->get_all_error_data( 'status' ) );
		}

		return wp_send_json_success();
	}

	/**
	 * Run the progress action
	 *
	 * @return bool|\WP_Error
	 */
	public function progressAction() {
		// verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'wp_rest' ) ) {
			return new \WP_Error( 'invalid', 'Nonce invalid', array( 'status' => 403 ) );
		}

		// video id is required
		if ( empty( $_POST['id'] ) ) {
			return new \WP_Error( 'invalid', 'You must provide a valid video id', array( 'status' => 400 ) );
		}

		// must have a valid percentage
		if ( ! isset( $_POST['percent'] ) ) {
			return new \WP_Error( 'invalid', 'You must provide a valid percentage', array( 'status' => 400 ) );
		}

		$id         = (int) $_POST['id'];
		$percent    = (int) $_POST['percent'];
		$visit_time = isset( $_POST['visit_time'] ) ? (int) $_POST['visit_time'] : false;

		/**
		 * Progress event, sends video id and percent progress
		 */
		do_action( 'presto_player_progress', $id, $percent, $visit_time );

		// success
		return true;
	}
}
