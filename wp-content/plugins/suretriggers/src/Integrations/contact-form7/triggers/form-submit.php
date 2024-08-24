<?php
/**
 * ContactForm7FormSubmit.
 * php version 5.6
 *
 * @category ContactForm7FormSubmit
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\ContactForm7\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'ContactForm7FormSubmit' ) ) :

	/**
	 * ContactForm7FormSubmit
	 *
	 * @category ContactForm7FormSubmit
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class ContactForm7FormSubmit {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'ContactForm7';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'contact_form7_form_submit';

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
				'common_action' => 'wpcf7_before_send_mail',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 99,
				'accepted_args' => 3,
			];
			return $triggers;

		}

		/**
		 * Trigger listener
		 *
		 * @param object $contact_form Form.
		 * @param array  $abort Result.
		 * @param object $submission Submission.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $contact_form, $abort, $submission ) {

			if ( is_object( $submission ) ) {
				if ( method_exists( $submission, 'get_posted_data' ) ) {
					$submited_data             = $submission->get_posted_data();
					$context['submitted_data'] = $submited_data;
					if ( method_exists( $submission, 'uploaded_files' ) ) {
						$files            = $submission->uploaded_files();
						$context['files'] = $files;
					}
				}
			
				if ( is_object( $contact_form ) ) {
					if ( method_exists( $contact_form, 'id' ) && method_exists( $contact_form, 'name' ) && method_exists( $contact_form, 'title' ) ) {
						$contact_form_data       = [
							'id'    => $contact_form->id(),
							'name'  => $contact_form->name(),
							'title' => $contact_form->title(),
						];
						$context['contact_form'] = $contact_form->id();
						$context['form_data']    = $contact_form_data;
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
	}

	/**
	 * Ignore false positive
	 *
	 * @psalm-suppress UndefinedMethod
	 */
	ContactForm7FormSubmit::get_instance();

endif;
