<?php
/**
 * BuddyBoss integration class file
 *
 * @package  SureTriggers
 * @since 1.0.0
 */

namespace SureTriggers\Integrations\BuddyBoss;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class BuddyBoss
 *
 * @package SureTriggers\Integrations\BuddyBoss
 */
class BuddyBoss extends Integrations {

	use SingletonLoader;

	/**
	 * ID of the integration
	 *
	 * @var string
	 */
	protected $id = 'BuddyBoss';

	/**
	 * BuddyBoss constructor.
	 */
	public function __construct() {
		add_filter(
			'bp_notifications_get_registered_components',
			[
				$this,
				'st_bdb_component',
			],
			99,
			2
		);

		add_filter(
			'bp_notifications_get_notifications_for_user',
			[
				$this,
				'st_bdb_notification_content',
			],
			99,
			8
		);
		parent::__construct();
	}

	/**
	 * Add Sure Triggers component
	 *
	 * @param array $component_names component names.
	 * @param array $active_components active components.
	 * @return array
	 */
	public function st_bdb_component( $component_names, $active_components ) {
		$component_names = ! is_array( $component_names ) ? [] : $component_names;
		array_push( $component_names, 'suretriggers' );
		return $component_names;
	}

	/**
	 * Update notification Content for SureTrigger Notifications.
	 *
	 * @param string $content content.
	 * @param int    $item_id item id.
	 * @param int    $secondary_item_id secondary item id.
	 * @param int    $action_item_count action item count.
	 * @param string $format format.
	 * @param string $component_action_name component action name.
	 * @param string $component_name component name.
	 * @param int    $id id.
	 * @return array|string
	 */
	public function st_bdb_notification_content( $content, $item_id, $secondary_item_id, $action_item_count, $format, $component_action_name, $component_name, $id ) {
		if ( 'sure-triggers_bb_notification' === $component_action_name ) {
			$notification_content = bp_notifications_get_meta( $id, 'st_notification_content' );
			$notification_link    = bp_notifications_get_meta( $id, 'st_notification_link' );

			if ( 'string' === $format ) {
				if ( '' !== $notification_link ) {
					$notification_content = '<a href="' . esc_url( $notification_link ) . '">' . $notification_content . '</a>';
				}
				return $notification_content;
			} elseif ( 'object' === $format ) {
				return [
					'text' => $notification_content,
					'link' => $notification_link,
				];
			}
		}

		return $content;
	}

	/**
	 * Check if content has links.
	 *
	 * @param string $content content.
	 * @return array|string
	 */
	public static function st_content_has_links( $content ) {
		// Define a regular expression pattern to match URLs.
		$pattern = '/<a\b[^>]*href=["\']([^"\'#]+)/i';

		// Use preg_match_all to find all links in the content.
		preg_match_all( $pattern, $content, $matches );
	 
		// Return the array of matched links.
		return isset( $matches[1] ) ? $matches[1] : [];
	}

	/**
	 * Check plugin is installed.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		if ( function_exists( 'buddypress' ) && isset( buddypress()->buddyboss ) && buddypress()->buddyboss ) {
			return true;
		} else {
			return false;
		}
	}
}

IntegrationsController::register( BuddyBoss::class );
