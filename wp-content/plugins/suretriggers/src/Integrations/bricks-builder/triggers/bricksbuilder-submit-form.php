<?php
/**
 * UserSubmitsBricksBuilderForm.
 * php version 5.6
 *
 * @category UserSubmitsBricksBuilderForm
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\BricksBuilder\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'UserSubmitsBricksBuilderForm' ) ) :

	/**
	 * UserSubmitsBricksBuilderForm
	 *
	 * @category UserSubmitsBricksBuilderForm
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 */
	class UserSubmitsBricksBuilderForm {

		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'BricksBuilder';

		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'user_submits_bricks_builder_form';

		use SingletonLoader;

		/**
		 * Constructor
		 *
		 * @since  1.0.0
		 */
		public function __construct() {
			add_filter( 'sure_trigger_register_trigger', [ $this, 'register' ] );
		}

		/**
		 * Register action.
		 *
		 * @param array $triggers trigger data.
		 * @return array
		 */
		public function register( $triggers ) {
			$triggers[ $this->integration ][ $this->trigger ] = [
				'label'         => __( 'Form Submitted', 'suretriggers' ),
				'action'        => 'user_submits_bricks_builder_form',
				'common_action' => 'bricksbuilder_after_form_submit',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 2,
			];

			return $triggers;
		}

		/**
		 * Change field label.
		 *
		 * @param string $label label.
		 * @return string
		 */
		public function modify_field_label( $label ) {
			$label = trim( $label );
			if ( strpos( $label, ' ' ) !== false ) {
				$label_str  = explode( ' ', $label );
				$result_str = array_map(
					function ( $val ) {
						return strtolower( $val );
					},
					$label_str
				);
				$label      = implode( '_', $result_str );
			} else {
				$label = strtolower( $label );
			}
			return $label;
		}

		/**
		 * Trigger listener
		 *
		 * @param array $response The response object.
		 * @param array $obj Bricks Form Object.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $response, $obj ) {
			$post_data = sanitize_post( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

			$files_data = $obj->get_uploaded_files();
			$context    = [];
			if ( ! empty( $post_data ) ) {
				$form_id            = ( isset( $post_data['formId'] ) ) ? sanitize_text_field( $post_data['formId'] ) : 0;
				$context['form_id'] = $form_id;
				$fields             = [];
				$form_fields        = [];
				$file_fields        = [];
				$file_field_labels  = [];
				foreach ( $post_data as $key => $value ) {
					if ( str_contains( $key, 'form-field-' ) ) {
						$field_id            = str_replace( 'form-field-', '', $key );
						$fields[ $field_id ] = $value;
					}
				}

				$bricks_settings = (array) get_option( BRICKS_DB_GLOBAL_SETTINGS );
				if ( array_key_exists( 'postTypes', $bricks_settings ) ) {
					$bricks_posts = $bricks_settings['postTypes'];
				} else {
					$bricks_posts = [];
				}
				$bricks_posts[] = 'bricks_template';

				$args = [
					'post_type'      => $bricks_posts,
					'post_status'    => 'publish',
					'posts_per_page' => -1,
				];

				$templates = get_posts( $args );

				if ( ! empty( $fields ) && ! empty( $templates ) ) { // Check if submitted form has fields.
					foreach ( $templates as $template ) {
						$bb_contents = get_post_meta( $template->ID, BRICKS_DB_PAGE_CONTENT, true ); // Fetch form contents.
						if ( ! empty( $bb_contents ) ) {
							foreach ( $bb_contents as $content ) {
								if ( $form_id === $content['id'] ) {
									$context['template_name'] = get_the_title( $template->ID );
									$form_fields              = ( isset( $content['settings']['fields'] ) ) ? $content['settings']['fields'] : [];
								}
							}
						}
					}

					if ( ! empty( $form_fields ) ) {
						foreach ( $form_fields as $field ) {
							if ( isset( $fields[ $field['id'] ] ) ) {
								$fd_label = ! empty( $field['label'] ) ? $field['label'] : $field['id'];
								$context[ $this->modify_field_label( $fd_label ) ] = $fields[ $field['id'] ];
							} else {
								$file_fields[]                     = $field['id'];
								$file_field_labels[ $field['id'] ] = $field['label'];
							}
						}
						if ( ! empty( $file_fields ) ) {
							foreach ( $file_fields as $file_field ) {
								$key   = 'form-field-' . $file_field;
								$label = $file_field_labels[ $file_field ];
								$urls  = [];
								if ( isset( $files_data[ $key ] ) && is_array( $files_data[ $key ] ) ) {
									foreach ( $files_data[ $key ] as $value ) {
										$urls[] = $value['url'];
									}
								}
								$context[ $this->modify_field_label( $label ) ] = $urls;
							}
						}
					}
				}
			}

			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger' => $this->trigger,
					'context' => $context,
				]
			);
		}
	}

	UserSubmitsBricksBuilderForm::get_instance();

endif;
