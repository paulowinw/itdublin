<?php
/**
 * RemoveTagFromContact.
 * php version 5.6
 *
 * @category RemoveTagFromContact
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\FluentCRM\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;

/**
 * RemoveTagFromContact
 *
 * @category RemoveTagFromContact
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class RemoveTagFromContact extends AutomateAction {


	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'FluentCRM';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'fluentcrm_remove_tag_from_contact';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {

		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Remove Tag', 'suretriggers' ),
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
	 * @param array $selected_options selectedOptions.
	 * @return array
	 * @throws Exception Exception.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		$context     = [];
		$contact_api = FluentCrmApi( 'contacts' );

		$contact = $contact_api->getContact( trim( $selected_options['contact_email'] ) );

		if ( is_null( $contact ) ) {
			throw new Exception( 'Invalid contact.' );
		}

		$tag_ids   = [];
		$tag_names = [];
		if ( is_array( $selected_options['tag_id'] ) && ! empty( $selected_options['tag_id'] ) ) {
			foreach ( $selected_options['tag_id'] as $tag ) {
				$tag_ids[]   = $tag['value'];
				$tag_names[] = esc_html( $tag['label'] );
			}
		}

		$contact->detachTags( $tag_ids );

		$context['tag_name']       = implode( ',', $tag_names );
		$context['full_name']      = $contact->full_name;
		$context['first_name']     = $contact->first_name;
		$context['last_name']      = $contact->last_name;
		$context['contact_owner']  = $contact->contact_owner;
		$context['company_id']     = $contact->company_id;
		$context['email']          = $contact->email;
		$context['address_line_1'] = $contact->address_line_1;
		$context['address_line_2'] = $contact->address_line_2;
		$context['postal_code']    = $contact->postal_code;
		$context['city']           = $contact->city;
		$context['state']          = $contact->state;
		$context['country']        = $contact->country;
		$context['phone']          = $contact->phone;
		$context['status']         = $contact->status;
		$context['contact_type']   = $contact->contact_type;
		$context['source']         = $contact->source;
		$context['date_of_birth']  = $contact->date_of_birth;
		return $context;
	}

}

RemoveTagFromContact::get_instance();
