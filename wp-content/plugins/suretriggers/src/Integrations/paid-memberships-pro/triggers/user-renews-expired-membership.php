<?php
/**
 * UserRenewsExpiredMembership.
 * php version 5.6
 *
 * @category UserRenewsExpiredMembership
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\PaidMembershipsPro\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;
use PMPro_Membership_Level;

if ( ! class_exists( 'UserRenewsExpiredMembership' ) ) :

	/**
	 * UserRenewsExpiredMembership
	 *
	 * @category UserRenewsExpiredMembership
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class UserRenewsExpiredMembership {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'PaidMembershipsPro';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'user_renews_expired_membership';

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
				'label'         => __( 'A user renews an expired membership', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'pmpro_before_change_membership_level',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 100,
				'accepted_args' => 4,
			];
			return $triggers;

		}

		/**
		 * Trigger listener
		 *
		 * @param int   $level_id ID of the level changed to.
		 * @param int   $user_id ID of the user changed.
		 * @param array $old_levels array of prior levels the user belonged to.
		 * @param int   $cancel_level ID of the level being cancelled if specified.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $level_id, $user_id, $old_levels, $cancel_level ) {

			if ( empty( $user_id ) || empty( $level_id ) ) {
				return;
			}

			$expired_levels = [];

			foreach ( $old_levels as $old_level ) {
				if ( ! empty( $old_level->enddate ) ) {
					$todays_date     = strtotime( current_time( 'mysql' ) );
					$expiration_date = $old_level->enddate;
					$time_left       = $expiration_date - $todays_date;

					// Is the membership expired.
					if ( $time_left <= 0 ) {
						if ( property_exists( $old_level, 'ID' ) ) {
							// Access the property here.
							$expired_levels[] = $old_level->ID;
						}
					}
				}
			}

			// The level being added to the user's levels must already be part of there expired level.
			if ( in_array( $level_id, $expired_levels ) ) {
				foreach ( $expired_levels as $level ) {
					if ( class_exists( 'PMPro_Membership_Level' ) ) {
						$membership_level = new PMPro_Membership_Level();
						$level_data       = $membership_level->get_membership_level( $level );
						$context['level'] = $level_data;
					}
					$context['user']          = WordPress::get_user_context( $user_id );
					$context['membership_id'] = $level;
					AutomationController::sure_trigger_handle_trigger(
						[
							'trigger' => $this->trigger,
							'context' => $context,
						]
					);
				}
			}
		}
	}

	/**
	 * Ignore false positive
	 *
	 * @psalm-suppress UndefinedMethod
	 */
	UserRenewsExpiredMembership::get_instance();

endif;
