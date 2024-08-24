<?php
/**
 * TicketClosedProductFluentSupport.
 * php version 5.6
 *
 * @category TicketClosedProductFluentSupport
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\FluentSupport\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;
use FluentSupport\App\Models\Ticket;

if ( ! class_exists( 'TicketClosedProductFluentSupport' ) ) :

	/**
	 * TicketClosedProductFluentSupport
	 *
	 * @category TicketClosedProductFluentSupport
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class TicketClosedProductFluentSupport {

		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'FluentSupport';

		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'ticket_closed_product_fluent_support';

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
				'label'         => __( 'Ticket Closed for Product', 'suretriggers' ),
				'action'        => 'ticket_created_fluent_support',
				'common_action' => 'fluent_support/ticket_closed',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 2,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param object $ticket ticket.
		 * @param object $customer customer.
		 *
		 * @return void
		 */
		public function trigger_listener( $ticket, $customer ) {

			if ( ! class_exists( '\FluentSupport\App\Models\Person' ) ) {
				return;
			}

			$context = array_merge(
				[
					'ticket'   => $ticket,
					'customer' => $customer,
				]
			);

			if ( ! class_exists( '\FluentSupport\App\Models\Ticket' ) ) {
				return;
			}

			if ( $ticket instanceof Ticket ) {
				$context['ticket_product_id'] = $ticket->product_id;
				$person                       = \FluentSupport\App\Models\Person::where( 'id', $ticket->closed_by )->first();
				$context['person_id']         = $person->person_type;
				if ( method_exists( $ticket, 'customData' ) ) {
					$context['custom_fields'] = $ticket->customData();
				}
				$context['ticket_link'] = admin_url( "admin.php?page=fluent-support#/tickets/{$ticket->id}/view" );
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
	TicketClosedProductFluentSupport::get_instance();

endif;
