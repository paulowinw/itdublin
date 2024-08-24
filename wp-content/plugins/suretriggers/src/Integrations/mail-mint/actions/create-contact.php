<?php
/**
 * CreateContact.
 * php version 5.6
 *
 * @category CreateContact
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\MailMint\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use MintMailPro\App\Actions\WebHooks;
use Mint\MRM\DataBase\Models\ContactModel;

/**
 * CreateContact
 *
 * @category CreateContact
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class CreateContact extends AutomateAction {


	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'MailMint';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'mail_mint_create_contact';

	use SingletonLoader;

	/**
	 * Register an action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {

		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Create Contact', 'suretriggers' ),
			'action'   => $this->action,
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
	 * @param array $selected_options selected_options.
	 *
	 * @return array|void
	 *
	 * @throws Exception Exception.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		if ( ! class_exists( 'MintMailPro\App\Actions\WebHooks' ) || ! class_exists( 'Mint\MRM\DataBase\Models\ContactModel' ) ) {
			return;
		}
		$post_data          = [];
		$hook_setting       = [];
		$post_data['email'] = ! empty( $selected_options['email'] ) ? $selected_options['email'] : '';
		if ( isset( $selected_options['first_name'] ) && ! empty( $selected_options['first_name'] ) ) {
			$post_data['first_name'] = $selected_options['first_name'];
		}
		if ( isset( $selected_options['last_name'] ) && ! empty( $selected_options['last_name'] ) ) {
			$post_data['last_name'] = $selected_options['last_name'];
		}
		if ( isset( $selected_options['phone_number'] ) && ! empty( $selected_options['phone_number'] ) ) {
			$post_data['phone_number'] = $selected_options['phone_number'];
		}
		if ( isset( $selected_options['date_of_birth'] ) && ! empty( $selected_options['date_of_birth'] ) ) {
			$post_data['date_of_birth'] = $selected_options['date_of_birth'];
		}
		if ( isset( $selected_options['gender'] ) && ! empty( $selected_options['gender'] ) ) {
			$post_data['gender'] = $selected_options['gender'];
		}

		if ( isset( $selected_options['address_line_1'] ) && ! empty( $selected_options['address_line_1'] ) ) {
			$post_data['address_line_1'] = $selected_options['address_line_1'];
		}
		if ( isset( $selected_options['address_line_2'] ) && ! empty( $selected_options['address_line_2'] ) ) {
			$post_data['address_line_2'] = $selected_options['address_line_2'];
		}
		if ( isset( $selected_options['city'] ) && ! empty( $selected_options['city'] ) ) {
			$post_data['city'] = $selected_options['city'];
		}
		if ( isset( $selected_options['postal'] ) && ! empty( $selected_options['postal'] ) ) {
			$post_data['postal'] = $selected_options['postal'];
		}
		if ( isset( $selected_options['country'] ) && ! empty( $selected_options['country'] ) ) {
			$post_data['country'] = $selected_options['country'];
		}
		if ( isset( $selected_options['state'] ) && ! empty( $selected_options['state'] ) ) {
			$post_data['state'] = $selected_options['state'];
		}
		if ( isset( $selected_options['company'] ) && ! empty( $selected_options['company'] ) ) {
			$post_data['company'] = $selected_options['company'];
		}
		if ( isset( $selected_options['designation'] ) && ! empty( $selected_options['designation'] ) ) {
			$post_data['designation'] = $selected_options['designation'];
		}
		if ( isset( $selected_options['timezone'] ) && ! empty( $selected_options['timezone'] ) ) {
			$post_data['timezone'] = $selected_options['timezone'];
		}
		
		if ( ! empty( $selected_options['field_row'] ) ) {
			foreach ( $selected_options['field_row'] as $field ) {
				if ( is_array( $field ) && ! empty( $field ) ) {
					foreach ( $field as $key => $value ) {
						if ( false === strpos( $key, 'field_column' ) && '' !== $value ) {
							$post_data['meta_fields'][ $key ] = $value;
						}
					}
				}
			}
		}

		if ( isset( $selected_options['lists'] ) && ! empty( $selected_options['lists'] ) ) {
			$hook_setting['lists'] = array_column( $selected_options['lists'], 'value' );
		}
		if ( isset( $selected_options['tags'] ) && ! empty( $selected_options['tags'] ) ) {
			$hook_setting['tags'] = array_column( $selected_options['tags'], 'value' );
		}
		if ( isset( $selected_options['status'] ) && ! empty( $selected_options['status'] ) ) {
			$hook_setting['status'] = $selected_options['status'];
		}
		$webhook_class = new WebHooks();

		$contact = $webhook_class->create_contact( $post_data, $hook_setting ); // phpcs:ignore

		if ( ! $contact ) {
			throw new Exception( 'Something went wrong while creating contact.' );
		}
		
		return ContactModel::get( $contact['contact_id'] );
	}

}

CreateContact::get_instance();
