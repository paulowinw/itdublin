<?php
/**
 * ContactUpdatedFluentCRM.
 * php version 5.6
 *
 * @category ContactUpdatedFluentCRM
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\FluentCRM\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;
use FluentCrm\App\Models\Subscriber;

if ( ! class_exists( 'ContactUpdatedFluentCRM' ) ) :

	/**
	 * ContactUpdatedFluentCRM
	 *
	 * @category ContactUpdatedFluentCRM
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class ContactUpdatedFluentCRM {


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
		public $trigger = 'contact_updated_fluentcrm';

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
				'label'         => __( 'Contact Updated', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'fluentcrm_contact_updated',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 1,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param object $contact Contact.
		 * @return void
		 */
		public function trigger_listener( $contact ) {
			if ( empty( $contact ) ) {
				return;
			}

			/**
			 *
			 * Ignore line
			 *
			 * @phpstan-ignore-next-line
			 */
			$subscriber      = Subscriber::with( [ 'tags', 'lists' ] )->find( $contact->id );
			$customer_fields = $subscriber->custom_fields();

			$context['contact']['details'] = $subscriber;
			$context['contact']['custom']  = $customer_fields;

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
	ContactUpdatedFluentCRM::get_instance();

endif;
