<?php
/**
 * SliceCommissionCreated.
 * php version 5.6
 *
 * @category SliceCommissionCreated
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\SliceWP\Triggers;

use SureTriggers\Controllers\AutomationController;
use Exception;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'SliceCommissionCreated' ) ) :

	/**
	 * SliceCommissionCreated
	 *
	 * @category SliceCommissionCreated
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class SliceCommissionCreated {

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
		public $trigger = 'slicewp_new_commission';

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
				'label'         => __( 'Commission Created', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'slicewp_insert_commission',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 2,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param int   $commission_id Commission ID.
		 * @param array $commission_data Commission Data.
		 * @throws Exception Exception.
		 * @return void
		 */
		public function trigger_listener( $commission_id, $commission_data ) {
			if ( ! function_exists( 'slicewp_get_affiliate' ) || ! function_exists( 'slicewp_get_commission' ) ) {
				throw new Exception( 'Slicewp functions not found.' );
			}
			$commission                       = slicewp_get_commission( $commission_id );
			$affiliate_id                     = $commission->get( 'affiliate_id' );
			$affiliate                        = slicewp_get_affiliate( $affiliate_id );
			$user_id                          = $affiliate->get( 'user_id' );
			$commission_data['commission_id'] = $commission_id;
			$context                          = array_merge(
				[ 'user' => WordPress::get_user_context( $user_id ) ],
				$commission_data
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
	SliceCommissionCreated::get_instance();

endif;
