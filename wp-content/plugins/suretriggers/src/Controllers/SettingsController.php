<?php
/**
 * SettingsController.
 * php version 5.6
 *
 * @category SettingsController
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Controllers;

use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'SettingsController' ) ) :

	/**
	 * SettingsController
	 *
	 * @category SettingsController
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 */
	class SettingsController {

		use SingletonLoader;

		/**
		 * SettingsController constructor.
		 *
		 * @return void
		 */
		public function __construct() {
			add_action( 'wp_ajax_st_save_settings', [ $this, 'save_settings' ] );
			add_action( 'wp_ajax_st_settings_ajax_button', [ $this, 'ajax_button_clicked' ] );
			add_action( 'st_settings_clear_cache_clicked', [ $this, 'clear_cache' ] );
		}

		/**
		 * Save settings
		 *
		 * @return void
		 */
		public function save_settings() {
			$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';

			if ( ! $nonce || ! wp_verify_nonce( $nonce, 'st-nonce' ) ) {
				wp_send_json_error();
			}

			$settings_json  = sanitize_text_field( wp_unslash( isset( $_POST['settings'] ) ? $_POST['settings'] : '' ) );
			$settings       = json_decode( $settings_json, true );
			$saved_settings = OptionController::get_option( 'st_settings' );

			if ( ! empty( $saved_settings ) && is_array( $saved_settings ) ) {
				$settings = array_merge( $saved_settings, $settings );
			}

			do_action( 'st_settings_save_before', $settings );
			OptionController::set_option( 'st_settings', $settings );
			do_action( 'st_settings_saved', $settings );

			wp_send_json_success();
		}

		/**
		 * Ajax button clicked
		 *
		 * @return void
		 */
		public function ajax_button_clicked() {
			$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';

			if ( ! $nonce || ! wp_verify_nonce( $nonce, 'st-nonce' ) ) {
				wp_send_json_error();
			}

			$button_name  = sanitize_text_field( wp_unslash( isset( $_POST['button_name'] ) ? $_POST['button_name'] : '' ) );
			$button_value = sanitize_text_field( wp_unslash( isset( $_POST['button_value'] ) ? $_POST['button_value'] : '' ) );

			do_action( 'st_settings_ajax_button_clicked', $button_name, $button_value );
			do_action( "st_settings_{$button_name}_clicked", $button_value );

			wp_send_json_success();
		}

		/**
		 * Clearing cache
		 *
		 * @return void
		 */
		public function clear_cache() {
			delete_option( 'suretrigger_options' );
		}

		/**
		 * Get form fields
		 *
		 * @return array[]
		 */
		public static function get_fields() {
			return [
				'dashboard' => [
					'title'    => __( 'General', 'suretriggers' ),
					'current'  => true,
					'sections' => [
						[
							'title'    => __( 'General Settings', 'suretriggers' ),
							'subtitle' => '',
							'fields'   => [
								[
									[
										'type'            => 'ajax-button',
										'name'            => 'clear_cache',
										'value'           => 'yes',
										'label'           => __( 'Clear Cache', 'suretriggers' ),
										'description'     => __(
											'By clicking here, your current connection with the SureTriggers for this site will be set to default',
											'suretriggers'
										),
										'confirmation'    => [
											'required' => true,
											'title'    => __( 'Clear Cache', 'suretriggers' ),
											'message'  => __( 'Are you sure to clear cache?', 'suretriggers' ),
										],
										'message_pending' => __( 'Clearing the cache', 'suretriggers' ),
										'message_done'    => __( 'Cache cleared', 'suretriggers' ),
										'redirect_after_click' => true,
										'redirect_url'    => admin_url( 'admin.php?page=suretriggers' ),
									],
								],
							],
						],
					],
				],
			];
		}
	}

	SettingsController::get_instance();

endif;
