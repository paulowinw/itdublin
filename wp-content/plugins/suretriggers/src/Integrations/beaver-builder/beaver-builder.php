<?php
/**
 * BeaverBuilder core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\BeaverBuilder;

use FLBuilderLoader;
use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\BeaverBuilder
 */
class BeaverBuilder extends Integrations {

	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'BeaverBuilder';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		add_action( 'fl_module_contact_form_after_send', [ $this, 'bb_after_contact_form_submit' ], 10, 6 );
		add_action( 'fl_builder_subscribe_form_submission_complete', [ $this, 'bb_after_subscription_form_submit' ], 10, 6 );
		parent::__construct();
	}

	/**
	 * On contact form submit.
	 *
	 * @param string $mailto mailto.
	 * @param string $subject subject.
	 * @param string $template template.
	 * @param array  $headers headers.
	 * @param array  $settings settings.
	 * @param string $result result.
	 * @return void
	 */
	public function bb_after_contact_form_submit( $mailto, $subject, $template, $headers, $settings, $result ) {
		$context = [];
		if ( ! $result ) {
			return;
		}

		if ( ! isset( $_POST['node_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return;
		}

		$node_id = sanitize_text_field( wp_unslash( $_POST['node_id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		$str              = str_replace( [ "\r", "\n" ], ' ', $template );
		$template_message = explode( ':  ', $str );
		$template_fields  = [];

		$template_fields = array_column(
			array_map(
				function ( $v ) {
					return explode( ':', $v );
				},
				explode( "\r\n", $template )
			),
			1,
			0
		);

		$template_fields['Message'] = $template_message[1];

		if ( empty( $template_fields ) ) {
			return;
		}
		$context['form_id']       = $node_id;
		$context['contact_name']  = $template_fields['Name'];
		$context['subject']       = $subject;
		$context['contact_email'] = $template_fields['Email'];
		$context['message']       = $template_fields['Message'];
		if ( isset( $template_fields['Phone'] ) ) {
			$context['contact_phone'] = $template_fields['Phone'];
		}
		do_action( 'suretriggers_bb_after_form_submit', $context );
	}

	/**
	 * On form submit.
	 *
	 * @param array  $response response.
	 * @param array  $settings settings.
	 * @param string $email email.
	 * @param string $name name.
	 * @param int    $template_id template id.
	 * @param int    $post_id post id.
	 * @return void
	 */
	public function bb_after_subscription_form_submit( $response, $settings, $email, $name, $template_id, $post_id ) {
		$context = [];

		if ( $response['error'] ) {
			return;
		}

		if ( ! isset( $_POST['node_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return;
		}

		$node_id                     = sanitize_text_field( wp_unslash( $_POST['node_id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$context['form_id']          = $node_id;
		$context['subscriber_name']  = $name;
		$context['subscriber_email'] = $email;
		do_action( 'suretriggers_bb_after_form_submit', $context );
	}

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( FLBuilderLoader::class );
	}
}

IntegrationsController::register( BeaverBuilder::class );
