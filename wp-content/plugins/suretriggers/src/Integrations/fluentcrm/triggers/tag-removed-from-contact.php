<?php
/**
 * TagRemovedFromContact.
 * php version 5.6
 *
 * @category TagRemovedFromContact
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\FluentCRM\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\FluentCRM\FluentCRM;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'TagRemovedFromContact' ) ) :

	/**
	 * TagRemovedFromContact
	 *
	 * @category TagRemovedFromContact
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class TagRemovedFromContact {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'FluentCRM';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'tag_removed_from_contact';

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
				'label'         => __( 'Tag Removed', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'fluentcrm_contact_removed_from_tags',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 2,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param array $tag_ids Tag IDs.
		 * @param mixed $contact Contact.
		 * @return void
		 */
		public function trigger_listener( $tag_ids, $contact ) {

			if ( empty( $tag_ids ) ) {
				return;
			}
			$contact = $contact->toArray();
			$context = [];
			foreach ( $tag_ids as $key => $tag_id ) {
				$context['tag_id']  = $tag_id;
				$context['contact'] = $contact;
				$context['tag']     = $contact['tags'][ $key ];
				unset( $context['contact']['tags'] );
				AutomationController::sure_trigger_handle_trigger(
					[
						'trigger' => $this->trigger,
						'context' => $context,
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
	TagRemovedFromContact::get_instance();

endif;
