<?php
/**
 * RemoveTagsFromContact.
 * php version 5.6
 *
 * @category RemoveTagsFromContact
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\FunnelKitAutomations\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Integrations\FunnelKitAutomations\FunnelKitAutomations;
use SureTriggers\Traits\SingletonLoader;

/**
 * RemoveTagsFromContact
 *
 * @category RemoveTagsFromContact
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class RemoveTagsFromContact extends AutomateAction {


	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'FunnelKitAutomations';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'funnel_kit_automations_remove_tags_from_contact';

	use SingletonLoader;

	/**
	 * Register an action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {

		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Remove Tag(s) from Contact', 'suretriggers' ),
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
	 * @return array
	 *
	 * @throws Exception Exception.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {

		if ( ! class_exists( 'BWFCRM_Contact' ) ) {
			throw new Exception( 'Plugin not installed correctly.' );
		}

		$email = sanitize_email( $selected_options['contact_email'] );

		if ( ! is_email( $email ) ) {
			throw new Exception( 'Invalid email.' );
		}

		$tag_ids = $selected_options['tag_ids'];

		$tags_to_add = [];
		foreach ( $tag_ids as $tag ) {
			$tags_to_add[] = $tag['value'];
		}

		$bwfcm_contact = new \BWFCRM_Contact( $email );

		$bwfcm_contact->remove_tags( $tags_to_add );

		if ( $bwfcm_contact->contact->get_id() === 0 ) {
			throw new Exception( 'Contact not exists with this email.' );
		}

		$bwfcm_contact->save();

		return FunnelKitAutomations::get_contact_context( $bwfcm_contact->contact );
	}

}

RemoveTagsFromContact::get_instance();
