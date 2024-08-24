<?php
/**
 * ListContacts.
 * php version 5.6
 *
 * @category ListContacts
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\MailMint\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use Mint\MRM\DataBase\Models\ContactModel;
use Exception;

/**
 * ListContacts
 *
 * @category ListContacts
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class ListContacts extends AutomateAction {

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
	public $action = 'mail_mint_get_all_contacts';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'List Contacts', 'suretriggers' ),
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
	 *
	 * @return array|void
	 * @throws Exception Error.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		if ( ! class_exists( 'Mint\MRM\DataBase\Models\ContactModel' ) ) {
			return;
		}
		$limit = 20;
		if ( isset( $selected_options['limit'] ) && ! empty( $selected_options['limit'] ) ) {
			$limit = $selected_options['limit'];
		}
		$contacts = ContactModel::get_all( 0, $limit );
		if ( ! empty( $contacts ) ) {
			return $contacts['data'];
		} else {
			return [];
		}
		
	}
	
	

}

ListContacts::get_instance();
