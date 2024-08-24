<?php
/**
 * BuddyPress core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\BuddyPress;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\BuddyPress
 */
class BuddyPress extends Integrations {


	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'BuddyPress';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'BuddyPress', 'suretriggers' );
		$this->description = __( 'A WordPress plugin that lets you gamify your WordPress website.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/buddypress.png';
		add_filter(
			'bp_notifications_get_registered_components',
			[
				$this,
				'st_bp_component',
			],
			10,
			2
		);

		// BP notification content.
		add_filter(
			'bp_notifications_get_notifications_for_user',
			[
				$this,
				'uo_bp_notification_content',
			],
			10,
			8
		);
		parent::__construct();
	}

	/**
	 * SureTrigger BuddyPress component.
	 * 
	 * @param array $component_names components name.
	 * @param array $active_components active_components.
	 * 
	 * @return array
	 */
	public function st_bp_component( $component_names, $active_components ) {

		$component_names = ! is_array( $component_names ) ? [] : $component_names;
		array_push( $component_names, 'suretriggers' );

		return $component_names;
	}

	/**
	 * SureTrigger BuddyPress Notification content.
	 * 
	 * @param string $content Component action. Deprecated. Do not do checks
	 *     against this! Use the 6th parameter instead -
	 *     $component_action_name.
	 * @param int    $item_id Notification item ID.
	 * @param int    $secondary_item_id Notification secondary item ID.
	 * @param int    $action_item_count Number of notifications with the same
	 *        action.
	 * @param string $format Format of return. Either 'string' or 'object'.
	 * @param string $component_action_name Canonical notification action.
	 * @param string $component_name Notification component ID.
	 * @param int    $id Notification ID.
	 *
	 * @return string|array
	 */
	public function uo_bp_notification_content( $content, $item_id, $secondary_item_id, $action_item_count, $format, $component_action_name, $component_name, $id ) {

		if ( 'suretriggers_bp_notification' === $component_action_name ) {

			if ( function_exists( 'bp_notifications_get_meta' ) ) {
				$notification_content = bp_notifications_get_meta( $id, 'st_notification_content' );
				$notification_link    = bp_notifications_get_meta( $id, 'st_notification_link' );
				if ( 'string' == $format ) {
					return $notification_content;
				} elseif ( 'object' == $format ) {
					return [
						'text' => $notification_content,
						'link' => $notification_link,
					];
				}
			}
		}

		return $content;
	}

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( 'BuddyPress' );
	}

}

IntegrationsController::register( BuddyPress::class );
