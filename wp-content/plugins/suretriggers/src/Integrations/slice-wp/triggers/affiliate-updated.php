<?php
/**
 * SliceAffiliateUpdated.
 * php version 5.6
 *
 * @category AffiliateCreated
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\SliceWP\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;
use Exception;

if ( ! class_exists( 'SliceAffiliateUpdated' ) ) :

	/**
	 * SliceAffiliateUpdated
	 *
	 * @category SliceAffiliateUpdated
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class SliceAffiliateUpdated {

		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'SliceWP';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'slicewp_update_affiliate';

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
		 *
		 * @return array
		 */
		public function register( $triggers ) {
			$triggers[ $this->integration ][ $this->trigger ] = [
				'label'         => __( 'Affiliate Updated', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'slicewp_update_affiliate',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 2,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param int   $affiliate_id Affiliate ID.
		 * @param array $affiliate_data Affiliate Data.
		 * @throws Exception Exception.
		 * @return void
		 */
		public function trigger_listener( $affiliate_id, $affiliate_data ) { 
			// Get the affiliate user ID.
			if ( ! function_exists( 'slicewp_get_affiliate' ) ) {
				throw new Exception( 'Slicewp functions not found.' );
			}
			$affiliate                      = slicewp_get_affiliate( $affiliate_id );
			$user_id                        = $affiliate->get( 'user_id' );
			$affiliate_data['id']           = $affiliate_id;
			$affiliate_data['affiliate_id'] = $affiliate_id;
			$context                        = array_merge(
				[ 'user' => WordPress::get_user_context( $user_id ) ],
				$affiliate_data
			);
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
	SliceAffiliateUpdated::get_instance();

endif;
