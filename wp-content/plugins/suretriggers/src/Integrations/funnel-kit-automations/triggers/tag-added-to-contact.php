<?php
/**
 * TagAddedToContactFunnelKitAutomations.
 * php version 5.6
 *
 * @category TagAddedToContactFunnelKitAutomations
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

if ( ! class_exists( 'TagAddedToContactFunnelKitAutomations' ) ) :

	/**
	 * TagAddedToContactFunnelKitAutomations
	 *
	 * @category TagAddedToContactFunnelKitAutomations
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class TagAddedToContactFunnelKitAutomations {

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
		public $trigger = 'tag_added_to_contact_funnel_kit_automations';

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
				'label'         => __( 'Tag Added to Contact', 'suretriggers' ),
				'action'        => 'tag_added_to_contact_funnel_kit_automations',
				'common_action' => 'bwfan_tags_added_to_contact',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 2,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param  mixed  $tags Added Tags.
		 * @param  object $bwfcm_contact Contact.
		 *
		 * @return void
		 */
		public function trigger_listener( $tags, $bwfcm_contact ) {

			if ( ! isset( $bwfcm_contact->contact ) ) {
				return;
			}

			$contact_data = FunnelKitAutomations::get_contact_context( $bwfcm_contact->contact );

			if ( ! is_array( $tags ) && is_object( $tags ) && method_exists( $tags, 'get_id' ) ) {
				$tag_data = FunnelKitAutomations::get_tag_context( $tags->get_id() );

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

			// @phpstan-ignore-next-line
			foreach ( $tags as $tag ) {
				if ( ! is_object( $tag ) || ! method_exists( $tag, 'get_id' ) ) {
					continue;
				}

				$tag_data = FunnelKitAutomations::get_tag_context( $tag->get_id() );

				if ( empty( $tag_data ) ) {
					continue;
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
	TagAddedToContactFunnelKitAutomations::get_instance();

endif;
