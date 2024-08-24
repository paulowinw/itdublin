<?php
/**
 * GroupUpdated.
 * php version 5.6
 *
 * @category GroupUpdated
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\SureMembers\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'GroupUpdated' ) ) :

	/**
	 * GroupUpdated
	 *
	 * @category GroupUpdated
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class GroupUpdated {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'SureMembers';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'suremember_updated_group';

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
				'label'         => __( 'Group Updated', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'suremembers_after_submit_form',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 1,
			];
			return $triggers;

		}

		/**
		 * Trigger listener
		 *
		 * @param int $group_id The group id.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $group_id ) {
			if ( empty( $group_id ) ) {
				return;
			}
			$group = sanitize_post( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

			$context['group']    = array_merge( WordPress::get_post_context( $group_id ), sanitize_post( isset( $group['suremembers_post'] ) ? $group['suremembers_post'] : [] ) );
			$context['group_id'] = $group_id;
			unset( $context['group']['ID'] ); //phpcs:ignore
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
	GroupUpdated::get_instance();

endif;
