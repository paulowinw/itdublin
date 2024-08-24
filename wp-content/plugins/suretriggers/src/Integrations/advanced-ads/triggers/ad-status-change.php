<?php
/**
 * AdStatusChange.
 * php version 5.6
 *
 * @category AdStatusChange
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\AdvancedAds\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;

if ( ! class_exists( 'AdStatusChange' ) ) :

	/**
	 * AdStatusChange
	 *
	 * @category AdStatusChange
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class AdStatusChange {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'AdvancedAds';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'ad_status_change';

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
				'label'         => __( 'Ad Status Changed', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => [ 'transition_post_status' ],
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 3,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param string $ad_new_status Ad New Status.
		 * @param object $ad_old_status Ad Old Status.
		 * @param object $ad Data.
		 * @return void
		 */
		public function trigger_listener( $ad_new_status, $ad_old_status, $ad ) {

			if ( property_exists( $ad, 'ID' ) ) {
				if ( '' == $ad->ID ) {
					return;
				}
			}

			if ( property_exists( $ad, 'ID' ) ) {
				$context                  = WordPress::get_post_context( $ad->ID );
				$context['ad_id']         = $ad->ID;
				$context['ad_old_status'] = $ad_old_status;
				$context['ad_new_status'] = $ad_new_status;
	
				AutomationController::sure_trigger_handle_trigger(
					[
						'trigger' => $this->trigger,
						'context' => $context,
					]
				);
			}
		}
	}

	/**
	 * Ignore false positive
	 *
	 * @psalm-suppress UndefinedMethod
	 */
	AdStatusChange::get_instance();

endif;
