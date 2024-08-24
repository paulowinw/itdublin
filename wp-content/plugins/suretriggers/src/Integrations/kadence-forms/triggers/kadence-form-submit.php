<?php
/**
 * KadenceFormSubmit.
 * php version 5.6
 *
 * @category KadenceFormSubmit
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\KadenceForms\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'KadenceFormSubmit' ) ) :

	/**
	 * KadenceFormSubmit
	 *
	 * @category KadenceFormSubmit
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class KadenceFormSubmit {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'KadenceForms';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'kadenceform_submitted';

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
				'label'         => __( 'Form Submitted', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'kadence_blocks_form_submission',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 4,
			];
			return $triggers;

		}

		/**
		 * Trigger listener
		 *
		 * @param array  $form_args Form submission after validation.
		 * @param array  $fields    Form data.
		 * @param object $form_id   Form object.
		 * @param object $post_id   Form object.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $form_args, $fields, $form_id, $post_id ) {
			$simple_entry = [
				'form_id' => $form_id,
				'post_id' => $post_id,
			];

			foreach ( $fields as $key => $data ) {
				$value                  = $data['value'];
				$value                  = explode( ', ', $data['value'] );
				$label                  = str_replace( [ ' ', '-' ], '_', strtolower( $data['label'] ) );
				$simple_entry[ $label ] = $value;
			}

			$context['kadence_form'] = $form_id;
			$context['entry']        = $simple_entry;

			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger'    => $this->trigger,
					'wp_user_id' => ap_get_current_user_id(),
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
	KadenceFormSubmit::get_instance();

endif;
