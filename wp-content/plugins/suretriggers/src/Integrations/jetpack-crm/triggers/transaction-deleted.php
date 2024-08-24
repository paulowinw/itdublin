<?php
/**
 * TransactionDeletedJetpackCRM.
 * php version 5.6
 *
 * @category TransactionDeletedJetpackCRM
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\JetpackCRM\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'TransactionDeletedJetpackCRM' ) ) :

	/**
	 * TransactionDeletedJetpackCRM
	 *
	 * @category TransactionDeletedJetpackCRM
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class TransactionDeletedJetpackCRM {

		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'JetpackCRM';

		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'transaction_deleted_jetpack_crm';

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
				'label'         => __( 'Transaction Deleted', 'suretriggers' ),
				'action'        => 'transaction_deleted_jetpack_crm',
				'common_action' => 'zbs_delete_transaction',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 1,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param int|string $transaction_id transaction ID.
		 *
		 * @return void
		 */
		public function trigger_listener( $transaction_id ) {
			if ( empty( $transaction_id ) ) {
				return;
			}

			$context = [
				'transaction_id' => $transaction_id,
			];

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
	TransactionDeletedJetpackCRM::get_instance();

endif;
