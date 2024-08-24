<?php
/**
 * TicketProductRepliedCustomerFluentSupport.
 * php version 5.6
 *
 * @category TicketProductRepliedCustomerFluentSupport
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

if ( ! class_exists( 'TicketProductRepliedCustomerFluentSupport' ) ) :

	/**
	 * TicketProductRepliedCustomerFluentSupport
	 *
	 * @category TicketProductRepliedCustomerFluentSupport
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class TicketProductRepliedCustomerFluentSupport {

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
		public $trigger = 'ticket_product_replied_customer_fluent_support';

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
				'label'         => __( 'Ticket for Product Replied by Customer', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'fluent_support/response_added_by_customer',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 3,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param object $response Response.
		 * @param object $ticket ticket.
		 * @param object $customer customer.
		 *
		 * @return void
		 */
		public function trigger_listener( $response, $ticket, $customer ) {

			$context = array_merge(
				[
					'reply'    => $response,
					'ticket'   => $ticket,
					'customer' => $customer,
				]
			);

			if ( ! class_exists( '\FluentSupport\App\Models\Ticket' ) ) {
				return;
			}

			if ( $ticket instanceof Ticket ) {
				$context['ticket_product_id'] = $ticket->product_id;
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
	TicketProductRepliedCustomerFluentSupport::get_instance();

endif;
