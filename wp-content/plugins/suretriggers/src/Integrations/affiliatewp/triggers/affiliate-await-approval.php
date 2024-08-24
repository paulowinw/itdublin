<?php
/**
 * AffiliateAwaitApproval.
 * php version 5.6
 *
 * @category AffiliateAwaitApproval
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\AffiliateWP\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\AffiliateWP\AffiliateWP;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'AffiliateAwaitApproval' ) ) :

	/**
	 * AffiliateAwaitApproval
	 *
	 * @category AffiliateAwaitApproval
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class AffiliateAwaitApproval {

		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'AffiliateWP';

		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'affwp_register_user_approval';

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
				'label'         => __( 'Affiliate Awaiting Approval', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'affwp_set_affiliate_status',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 3,
			];
			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param int    $affiliate_id Affiliate ID.
		 * @param string $status Affiliate status.
		 * @param string $old_status Affiliate old status.
		 * @return string|void
		 */
		public function trigger_listener( $affiliate_id, $status, $old_status ) {
			if ( 'pending' !== $status ) {
				return $status;
			}

			$user_id = affwp_get_affiliate_user_id( $affiliate_id );

			$affiliate = affwp_get_affiliate( $affiliate_id );

			$context = array_merge(
				WordPress::get_user_context( $user_id ),
				get_object_vars( $affiliate )
			);

			$context['status'] = $status;

			$user_id = ap_get_current_user_id();

			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger'    => $this->trigger,
					'wp_user_id' => $user_id,
					'context'    => $context,
				]
			);
		}
	}

	/**
	 * Ignore false positive
	 *
	 * @psalm-suppress UndefinedMethod
	 */
	AffiliateAwaitApproval::get_instance();

endif;
