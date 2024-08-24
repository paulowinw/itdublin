<?php
/**
 * ContactCustomFieldsUpdatedFluentCRM.
 * php version 5.6
 *
 * @category ContactCustomFieldsUpdatedFluentCRM
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

if ( ! class_exists( 'ContactCustomFieldsUpdatedFluentCRM' ) ) :

	/**
	 * ContactCustomFieldsUpdatedFluentCRM
	 *
	 * @category ContactCustomFieldsUpdatedFluentCRM
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class ContactCustomFieldsUpdatedFluentCRM {


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
		public $trigger = 'contact_custom_fields_updated_fluentcrm';

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
				'label'         => __( 'Contact Custom Fields Updated', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'fluentcrm_contact_custom_data_updated',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 3,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param array  $new_values New Values.
		 * @param object $subscriber Contact.
		 * @param array  $update_values Update Values.
		 * @return void
		 */
		public function trigger_listener( $new_values, $subscriber, $update_values ) {
			if ( empty( $update_values ) ) {
				return;
			}
			$context = [];
			if ( method_exists( $subscriber, 'custom_fields' ) ) {
				$custom_data = $subscriber->custom_fields();
			}
			if ( method_exists( $subscriber, 'toArray' ) ) {
				if ( ! empty( $custom_data ) ) {
					$subscriber                    = $subscriber->toArray();
					$context['contact']['details'] = $subscriber;
					foreach ( $new_values as $key => $field ) {
						if ( is_array( $field ) ) {
							$context['contact']['custom'][ $key ] = implode( ',', $field );
						} else {
							$context['contact']['custom'][ $key ] = $field;
						}
					}
					foreach ( $update_values as $key => $field ) {
						$context['field_id'] = $key;
						AutomationController::sure_trigger_handle_trigger(
							[
								'trigger' => $this->trigger,
								'context' => $context,
							]
						);
					}
				}
			}
		}
	}

	/**
	 * Ignore false positive
	 *
	 * @psalm-suppress UndefinedMethod
	 */
	ContactCustomFieldsUpdatedFluentCRM::get_instance();

endif;
