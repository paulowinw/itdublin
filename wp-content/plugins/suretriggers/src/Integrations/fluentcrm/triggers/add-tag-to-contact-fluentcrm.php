<?php
/**
 * AddTagToContactFluentCRM.
 * php version 5.6
 *
 * @category AddTagToContactFluentCRM
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

if ( ! class_exists( 'AddTagToContactFluentCRM' ) ) :

	/**
	 * AddTagToContactFluentCRM
	 *
	 * @category AddTagToContactFluentCRM
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class AddTagToContactFluentCRM {


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
		public $trigger = 'add_tag_contact_fluentcrm';

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
				'label'         => __( 'Tag Added', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'fluentcrm_contact_added_to_tags',
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
				$tag_key            = array_search( $tag_id, array_column( $contact['tags'], 'id' ) );
				$context['tag']     = $contact['tags'][ $tag_key ];
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
	AddTagToContactFluentCRM::get_instance();

endif;
