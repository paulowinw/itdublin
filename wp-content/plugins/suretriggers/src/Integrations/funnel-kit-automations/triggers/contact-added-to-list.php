<?php
/**
 * ContactAddedToListFunnelKitAutomations.
 * php version 5.6
 *
 * @category ContactAddedToListFunnelKitAutomations
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

if ( ! class_exists( 'ContactAddedToListFunnelKitAutomations' ) ) :

	/**
	 * ContactAddedToListFunnelKitAutomations
	 *
	 * @category ContactAddedToListFunnelKitAutomations
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class ContactAddedToListFunnelKitAutomations {

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
		public $trigger = 'contact_added_to_list_funnel_kit_automations';

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
				'label'         => __( 'Contact Added to List', 'suretriggers' ),
				'action'        => 'contact_added_to_list_funnel_kit_automations',
				'common_action' => 'bwfan_contact_added_to_lists',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 2,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param  mixed  $lists Added Lists.
		 * @param  object $bwfcm_contact Contact.
		 *
		 * @return void
		 */
		public function trigger_listener( $lists, $bwfcm_contact ) {

			if ( ! isset( $bwfcm_contact->contact ) ) {
				return;
			}

			$contact_data = FunnelKitAutomations::get_contact_context( $bwfcm_contact->contact );

			if ( ! is_array( $lists ) && is_object( $lists ) && method_exists( $lists, 'get_id' ) ) {

				$list_data = FunnelKitAutomations::get_list_context( $lists->get_id() );

				if ( empty( $list_data ) ) {
					return;
				}

				AutomationController::sure_trigger_handle_trigger(
					[
						'trigger' => $this->trigger,
						'context' => array_merge( $list_data, $contact_data ),
					]
				);
			}

			// @phpstan-ignore-next-line
			foreach ( $lists as $list ) {
				if ( ! is_object( $list ) || ! method_exists( $list, 'get_id' ) ) {
					continue;
				}

				$list_data = FunnelKitAutomations::get_list_context( $list->get_id() );

				if ( empty( $list_data ) ) {
					continue;
				}

				AutomationController::sure_trigger_handle_trigger(
					[
						'trigger' => $this->trigger,
						'context' => array_merge( $list_data, $contact_data ),
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
	ContactAddedToListFunnelKitAutomations::get_instance();

endif;
