<?php
/**
 * CompanyCreatedJetpackCRM.
 * php version 5.6
 *
 * @category CompanyCreatedJetpackCRM
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\JetpackCRM\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\JetpackCRM\JetpackCRM;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'CompanyCreatedJetpackCRM' ) ) :

	/**
	 * CompanyCreatedJetpackCRM
	 *
	 * @category CompanyCreatedJetpackCRM
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class CompanyCreatedJetpackCRM {

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
		public $trigger = 'company_created_jetpack_crm';

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
				'label'         => __( 'Company Created', 'suretriggers' ),
				'action'        => 'company_created_jetpack_crm',
				'common_action' => 'zbs_new_company',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 1,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param int|string $company_id company ID.
		 *
		 * @return void
		 */
		public function trigger_listener( $company_id ) {
			if ( empty( $company_id ) ) {
				return;
			}

			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger' => $this->trigger,
					'context' => JetpackCRM::get_company_context( $company_id ),
				]
			);
		}
	}

	/**
	 * Ignore false positive
	 *
	 * @psalm-suppress UndefinedMethod
	 */
	CompanyCreatedJetpackCRM::get_instance();

endif;
