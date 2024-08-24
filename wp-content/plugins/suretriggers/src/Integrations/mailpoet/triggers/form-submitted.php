<?php
/**
 * FormSubmitted.
 * php version 5.6
 *
 * @category FormSubmitted
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\MailPoet\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'FormSubmitted' ) ) :

	/**
	 * FormSubmitted
	 *
	 * @category FormSubmitted
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class FormSubmitted {

		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'MailPoet';

		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'mailpoet_form_submitted';

		use SingletonLoader;

		/**
		 * Constructor
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			add_filter( 'sure_trigger_register_trigger', [ $this, 'register' ] );
		}

		/**
		 * Register action.
		 *
		 * @param array $triggers trigger data.
		 *
		 * @return array
		 */
		public function register( $triggers ) {
			$triggers[ $this->integration ][ $this->trigger ] = [
				'label'         => __( 'Form Submitted', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'mailpoet_subscription_before_subscribe',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 3,
			];
			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param array  $data Appointment Data.
		 * @param array  $segment_ids Segment Ids.
		 * @param object $form Form object.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $data, $segment_ids, $form ) {
			if ( empty( $form ) ) {
				return;
			}

			$mailpoet = \MailPoet\API\API::MP( 'v1' );
			$fields   = $mailpoet->getSubscriberFields();

			// Form title.
			$context['form_title'] = esc_html( $form->getName() );

			// Form id.
			$context['form_id'] = $form->getId();

			// Form Fields.
			if ( ! empty( $fields ) ) {
				foreach ( $fields as $key => $field ) {
					if ( preg_match( '/[cf_]/i', $field['id'] ) ) {
						$context[ $field['name'] ] = $data[ $field['id'] ];
					} else {
						$context[ $field['id'] ] = $data[ $field['id'] ];
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
	FormSubmitted::get_instance();

endif;
