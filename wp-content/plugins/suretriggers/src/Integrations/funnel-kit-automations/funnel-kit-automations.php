<?php
/**
 * FunnelKitAutomations core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\FunnelKitAutomations;

use BWFCRM_Contact;
use BWFCRM_Fields;
use BWFCRM_Lists;
use BWFCRM_Tag;
use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\FunnelKitAutomations
 */
class FunnelKitAutomations extends Integrations {

	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'FunnelKitAutomations';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'FunnelKitAutomations', 'suretriggers' );
		$this->description = __( 'FunnelKit Automations is a WordPress Customer Support plugin.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/jetpackcrm.svg';

		parent::__construct();
	}

	/**
	 * Get List context data.
	 *
	 * @param int|string $list_id List ID.
	 * @return array
	 */
	public static function get_list_context( $list_id ) {
		if ( ! class_exists( 'BWFCRM_Lists' ) ) {
			return [];
		}

		$lists = BWFCRM_Lists::get_lists( [ $list_id ] );
		if ( count( $lists ) === 0 ) {
			return [];
		}
		return [
			'list_id'   => $lists[0]['ID'],
			'list_name' => $lists[0]['name'],
		];
	}

	/**
	 * Get Tag context data.
	 *
	 * @param int|string $tag_id Tag ID.
	 * @return array
	 */
	public static function get_tag_context( $tag_id ) {
		if ( ! class_exists( 'BWFCRM_Tag' ) ) {
			return [];
		}

		$tags = BWFCRM_Tag::get_tags( [ $tag_id ] );
		if ( count( $tags ) === 0 ) {
			return [];
		}
		return [
			'tag_id'   => $tags[0]['ID'],
			'tag_name' => $tags[0]['name'],
		];
	}

	/**
	 * Retrieve contact details from the given contact object.
	 *
	 * @access public
	 * @since 1.0
	 * @param Object $contact  Autonami contact object.
	 * @return array
	 */
	public static function get_contact_context( $contact ) {
		$tags  = $contact->get_tags(); // @phpstan-ignore-line
		$lists = $contact->get_lists(); // @phpstan-ignore-line

		$contact_tags = [];
		if ( is_array( $tags ) ) {
			foreach ( $tags as $key => $tag ) {
				$contact_tags[ $key ] = self::get_tag_context( $tag );
			}
		}

		$contact_lists = [];
		if ( is_array( $lists ) ) {
			foreach ( $lists as $key => $list ) {
				$contact_lists[ $key ] = self::get_list_context( $list );
			}
		}

		return [
			'contact_id'    => $contact->get_id(), // @phpstan-ignore-line
			'wpid'          => $contact->get_wpid(), // @phpstan-ignore-line
			'uid'           => $contact->get_uid(), // @phpstan-ignore-line
			'email'         => $contact->get_email(), // @phpstan-ignore-line
			'first_name'    => $contact->get_f_name(), // @phpstan-ignore-line
			'last_name'     => $contact->get_l_name(), // @phpstan-ignore-line
			'contact_no'    => $contact->contact_no(), // @phpstan-ignore-line
			'state'         => $contact->get_state(), // @phpstan-ignore-line
			'country'       => $contact->get_country(), // @phpstan-ignore-line
			'timezone'      => $contact->get_timezone(), // @phpstan-ignore-line
			'creation_date' => ! empty( $contact->get_creation_date() ) ? $contact->get_creation_date() : '', // @phpstan-ignore-line
			'last_modified' => ! empty( $contact->get_last_modified() ) ? $contact->get_last_modified() : '', // @phpstan-ignore-line
			'source'        => $contact->get_source(), // @phpstan-ignore-line
			'type'          => $contact->get_type(), // @phpstan-ignore-line
			'status'        => $contact->get_status(), // @phpstan-ignore-line
			'tags'          => $contact_tags,
			'lists'         => $contact_lists,
		];
	}

	/**
	 * Is Plugin depended on plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( 'BWFCRM_Contact' );
	}

}

IntegrationsController::register( FunnelKitAutomations::class );
