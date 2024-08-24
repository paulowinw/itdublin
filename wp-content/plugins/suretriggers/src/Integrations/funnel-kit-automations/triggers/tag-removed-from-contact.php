<?php
/**
 * TagRemovedFromContactFunnelKitAutomations.
 * php version 5.6
 *
 * @category TagRemovedFromContactFunnelKitAutomations
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\FunnelKitAutomations\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\FunnelKitAutomations\FunnelKitAutomations;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'TagRemovedFromContactFunnelKitAutomations' ) ) :

	/**
	 * TagRemovedFromContactFunnelKitAutomations
	 *
	 * @category TagRemovedFromContactFunnelKitAutomations
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class TagRemovedFromContactFunnelKitAutomations {

		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'FunnelKitAutomations';

		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'tag_removed_from_contact_funnel_kit_automations';

		use SingletonLoader;

		/**
		 * Constructor
		 *
		 * @since  1.0.0
		 */
		public function __construct() {
			add_filter( 'sure_trigger_register_trigger', [ $this, 'register' ] );
		}

		/**
		 * Register action.
		 *
		 * @param array $triggers trigger data.
		 * @return array
		 */
		public function register( $triggers ) {
			$triggers[ $this->integration ][ $this->trigger ] = [
				'label'         => __( 'Tag Removed from Contact', 'suretriggers' ),
				'action'        => 'tag_removed_from_contact_funnel_kit_automations',
				'common_action' => 'bwfan_tags_removed_from_contact',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 2,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param  array  $tags Removed Tags.
		 * @param  object $bwfcm_contact Contact.
		 *
		 * @return void
		 */
		public function trigger_listener( $tags, $bwfcm_contact ) {
			if ( ! isset( $bwfcm_contact->contact ) ) {
				return;
			}

			$contact_data = FunnelKitAutomations::get_contact_context( $bwfcm_contact->contact );

			foreach ( $tags as $tag_id ) {
				$tag_data = FunnelKitAutomations::get_tag_context( $tag_id );

				if ( empty( $tag_data ) ) {
					return;
				}

				AutomationController::sure_trigger_handle_trigger(
					[
						'trigger' => $this->trigger,
						'context' => array_merge( $tag_data, $contact_data ),
					]
				);
			}
		}
	}

	/**
	 * Ignore false positive
	 *
	 * @psalm-suppress UndefinedMethod
	 */
	TagRemovedFromContactFunnelKitAutomations::get_instance();

endif;
