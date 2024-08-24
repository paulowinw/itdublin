<?php
/**
 * AddRecipientsToCampaign.
 * php version 5.6
 *
 * @category AddRecipientsToCampaign
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\MailMint\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use Mint\MRM\DataBase\Models\CampaignModel;
use Mint\MRM\DataBase\Models\ContactGroupModel;

use Exception;

/**
 * AddRecipientsToCampaign
 *
 * @category AddRecipientsToCampaign
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class AddRecipientsToCampaign extends AutomateAction {

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
	public $action = 'mail_mint_add_recipients_to_campaign';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Add Recipients To Campaign', 'suretriggers' ),
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
		if ( ! class_exists( 'Mint\MRM\DataBase\Models\ContactGroupModel' ) || ! class_exists( 'Mint\MRM\DataBase\Models\CampaignModel' ) ) {
			return;
		}
		$campaign_id = $selected_options['campaign_id'] ? $selected_options['campaign_id'] : 0;
		if ( ! CampaignModel::is_campaign_exist( $campaign_id ) ) {
			throw new Exception( 'There is no campaign with provided id.' );
		}
		$selected_tags  = $selected_options['campaign_tags'] ? explode( ',', $selected_options['campaign_tags'] ) : [];
		$selected_lists = $selected_options['campaign_lists'] ? explode( ',', $selected_options['campaign_lists'] ) : [];
		$value          = [];
		foreach ( $selected_lists as $list ) {
			$lists            = ContactGroupModel::get_or_insert_contact_group_by_title( $list, 'lists' );
			$value['lists'][] = $lists;
		}

		foreach ( $selected_tags as $tag ) {
			$tags            = ContactGroupModel::get_or_insert_contact_group_by_title( $tag, 'tags' );
			$value['tags'][] = $tags;
		}
		CampaignModel::insert_or_update_campaign_meta( $campaign_id, 'recipients', maybe_serialize( $value ) );
		$campaign_data               = CampaignModel::get( $campaign_id );
		$campaign_data['meta_value'] = unserialize( $campaign_data['meta_value'] );
		return $campaign_data;
		
	}


}

AddRecipientsToCampaign::get_instance();
