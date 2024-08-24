<?php
/**
 * ContactStatusSetToSpecificStatusFluentCRM.
 * php version 5.6
 *
 * @category ContactStatusSetToSpecificStatusFluentCRM
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

if ( ! class_exists( 'ContactStatusSetToSpecificStatusFluentCRM' ) ) :

	/**
	 * ContactStatusSetToSpecificStatusFluentCRM
	 *
	 * @category ContactStatusSetToSpecificStatusFluentCRM
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class ContactStatusSetToSpecificStatusFluentCRM {


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
		public $trigger = 'contact_status_set_to_specific_status_fluentcrm';

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
				'label'         => __( 'Contact Status Set to Specific Status', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => [ 
					'fluentcrm_subscriber_status_to_subscribed',
					'fluentcrm_subscriber_status_to_pending',
					'fluentcrm_subscriber_status_to_unsubscribed',
					'fluentcrm_subscriber_status_to_bounced',
					'fluentcrm_subscriber_status_to_complained', 
				],
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 2,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param object $contact Contact.
		 * @param string $old_status Old Status.
		 * @return void
		 */
		public function trigger_listener( $contact, $old_status ) {
			if ( empty( $contact ) ) {
				return;
			}
			
			$context['old_status'] = $old_status;
			if ( method_exists( $contact, 'custom_fields' ) ) {
				$custom_data = $contact->custom_fields();
			}
			$context['contact']['details'] = $contact;
			/**
			 *
			 * Ignore line
			 *
			 * @phpstan-ignore-next-line
			 */
			$context['status'] = $contact->status;
			if ( ! empty( $custom_data ) ) {
				foreach ( $custom_data as $key => $field ) {
					if ( is_array( $field ) ) {
						$context['contact']['custom'][ $key ] = implode( ',', $field );
					} else {
						$context['contact']['custom'][ $key ] = $field;
					}
				}
			}
			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger' => $this->trigger,
					'context' => $context,
				]
			);
		}
	}

	/**
	 * Ignore false positive
	 *
	 * @psalm-suppress UndefinedMethod
	 */
	ContactStatusSetToSpecificStatusFluentCRM::get_instance();

endif;
