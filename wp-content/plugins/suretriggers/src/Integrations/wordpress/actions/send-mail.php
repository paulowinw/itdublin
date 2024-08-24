<?php
/**
 * SendMail.
 * php version 5.6
 *
 * @category SendMail
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\Wordpress\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

/**
 * SendMail
 *
 * @category SendMail
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class SendMail extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'WordPress';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'send_mail';

	use SingletonLoader;

	/**
	 * Register action.
	 *
	 * @param array $actions action data.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Send an email', 'suretriggers' ),
			'action'   => 'send_mail',
			'function' => [ $this, 'action_listener' ],
		];

		return $actions;
	}

	/**
	 * Action listener.
	 *
	 * @param int   $user_id user_id.
	 * @param int   $automation_id automation_id.
	 * @param array $fields fields.
	 * @param array $selected_options selectedOptions.
	 * @return array|mixed
	 * @throws Exception Exception.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		$result_arr = [];

		foreach ( $fields as $field ) {
			if ( ! isset( $field['name'] ) ) {
				continue;
			}

			$result_arr[ $field['name'] ] = isset( $selected_options[ $field['name'] ] ) ? $selected_options[ $field['name'] ] : '';
		}

		$result_arr['headers']   = [];
		$result_arr['headers'][] = 'Content-Type: text/html; charset=UTF-8';

		$cc_email   = isset( $selected_options['cc_email'] ) ? $selected_options['cc_email'] : '';
		$bcc_email  = isset( $selected_options['bcc_email'] ) ? $selected_options['bcc_email'] : '';
		$from_email = isset( $selected_options['from_email'] ) ? $selected_options['from_email'] : '';
		$from_name  = isset( $selected_options['from_name'] ) ? $selected_options['from_name'] : '';

		$to_email = $result_arr['to_email'];
		$is_valid = WordPress::validate_email( $to_email );

		if ( ! $is_valid->valid ) {
			if ( $is_valid->multiple ) {
				throw new Exception( 'One or more To email address is not valid' );
			} else {
				throw new Exception( 'To email address is not valid' );
			}
		}

		if ( ! empty( $from_email ) ) {
			$is_valid = WordPress::validate_email( $from_email );

			if ( ! $is_valid->valid ) {
				if ( $is_valid->multiple ) {
					throw new Exception( 'One or more From email address is not valid' );
				} else {
					throw new Exception( 'From email address is not valid' );
				}
			}
			if ( ! empty( $from_name ) ) {
				$result_arr['headers'][] = 'From: ' . $from_name . ' <' . $from_email . '>';
			} else {
				$result_arr['headers'][] = 'From: <' . $from_email . '>';
			}       
		}
		
		
		if ( ! empty( $cc_email ) ) {
			$is_valid = WordPress::validate_email( $cc_email );

			if ( ! $is_valid->valid ) {
				if ( $is_valid->multiple ) {
					throw new Exception( 'One or more CC email address is not valid' );
				} else {
					throw new Exception( 'CC email address is not valid' );
				}
			}

			$result_arr['headers'][] = 'CC: ' . $cc_email;
		}
		if ( ! empty( $bcc_email ) ) {
			$is_valid = WordPress::validate_email( $bcc_email );

			if ( ! $is_valid->valid ) {
				if ( $is_valid->multiple ) {
					throw new Exception( 'One or more BCC email address is not valid' );
				} else {
					throw new Exception( 'BCC email address is not valid' );
				}
			}

			$result_arr['headers'][] = 'BCC: ' . $cc_email;
		}
		
		$result = wp_mail( $to_email, $result_arr['subject'], $result_arr['email_body'], $result_arr['headers'], $attachments = [] ); //phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_mail_wp_mail

		if ( ! $result ) {
			throw new Exception( 'Email sending failed!' );
		}

		return $result_arr;
	}
}

SendMail::get_instance();
