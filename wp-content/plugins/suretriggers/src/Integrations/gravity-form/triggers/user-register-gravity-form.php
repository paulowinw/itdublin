<?php
/**
 * UserRegisterGravityForm.
 * php version 5.6
 *
 * @category UserRegisterGravityForm
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\GravityForms\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'UserRegisterGravityForm' ) ) :

	/**
	 * UserRegisterGravityForm
	 *
	 * @category UserRegisterGravityForm
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class UserRegisterGravityForm {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'GravityForms';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'user_register_gravityform';

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
				'label'         => __( 'User Register with Gravity Form', 'suretriggers' ),
				'action'        => 'user_register_gravityform',
				'common_action' => 'gform_user_registered',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 4,
			];

			return $triggers;

		}

		/**
		 * Trigger listener
		 *
		 * @param int     $user_id           The form object for the entry.
		 * @param integer $feed     The entry ID.
		 * @param array   $entry The entry object before being updated.
		 * @param array   $password The entry object before being updated.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $user_id, $feed, $entry, $password ) {
			
			$context['gravity_form']          = (int) $entry['form_id'];
			$context['entry_id']              = $entry['id'];
			$context['user_ip']               = $entry['ip'];
			$context['entry_source_url']      = $entry['source_url'];
			$context['entry_submission_date'] = $entry['date_created'];
			$context['user']                  = WordPress::get_user_context( $user_id );

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
	UserRegisterGravityForm::get_instance();

endif;
