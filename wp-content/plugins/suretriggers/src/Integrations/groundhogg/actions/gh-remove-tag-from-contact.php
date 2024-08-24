<?php
/**
 * GhRemoveTagFromContact.
 * php version 5.6
 *
 * @category GhRemoveTagFromContact
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\Groundhogg\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;

/**
 * GhRemoveTagFromContact
 *
 * @category GhRemoveTagFromContact
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class GhRemoveTagFromContact extends AutomateAction {


	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'Groundhogg';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'gh_removetag_from_contact';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {

		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Remove Tag from Contact', 'suretriggers' ),
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
	 * @return array|void
	 * @throws Exception Exception.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		$email  = sanitize_email( $selected_options['contact_email'] );
		$tag_id = $selected_options['tag_id'];

		if ( ! class_exists( '\Groundhogg\Plugin' ) ) {
			return [];
		}

		if ( is_email( $email ) ) {
			if ( 0 !== $tag_id ) {
				$contact = \Groundhogg\Plugin::$instance->utils->get_contact( $email, true );
				if ( ! $contact ) {
					throw new Exception( 'Contact not found with this email.' );
				}
	
				$tags_to_add = [ $tag_id ];
				$contact->remove_tag( $tags_to_add );
				$context['contact'] = $contact;
				return $context;
			}
		} else {
			throw new Exception( 'Enter valid email' );
		}
	}

}

GhRemoveTagFromContact::get_instance();
