<?php
/**
 * CompleteSection.
 * php version 5.6
 *
 * @category CompleteSection
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\LifterLMS\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\LifterLMS\LifterLMS;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;
use LLMS_Section;

/**
 * CompleteSection
 *
 * @category CompleteSection
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class CompleteSection {


	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'LifterLMS';

	/**
	 * Trigger name.
	 *
	 * @var string
	 */
	public $trigger = 'lifterlms_section_completed';

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
			'label'         => __( 'User complete section', 'suretriggers' ),
			'action'        => $this->trigger,
			'common_action' => 'lifterlms_section_completed',
			'function'      => [ $this, 'trigger_listener' ],
			'priority'      => 20,
			'accepted_args' => 2,
		];

		return $triggers;
	}

	/**
	 * Trigger listener
	 *
	 * @param int $user_id user id.
	 * @param int $section_id section id.
	 *
	 * @return void
	 */
	public function trigger_listener( $user_id, $section_id ) {
		if ( ! class_exists( 'LLMS_Section' ) ) {
			return;
		}

		$data                           = new \LLMS_Section( $section_id );
		$lessons                        = $data->get_lessons();
		$context                        = array_merge(
			WordPress::get_user_context( $user_id ),
			WordPress::get_post_context( $section_id )
		);
		$context['parent_course']       = $data->get( 'parent_course' );
		$context['parent_course_title'] = get_the_title( $data->get( 'parent_course' ) );
		if ( ! empty( $lessons ) ) {
			foreach ( $lessons as $key => $lesson ) {
				$context['section_lesson'][ $key ]       = $lesson->id;
				$context['section_lesson_title'][ $key ] = get_the_title( $lesson->id );
			}
		}
		$context['section_course']      = $data->get( 'parent_course' );
		$context['parent_course_title'] = get_the_title( $data->get( 'parent_course' ) );

		AutomationController::sure_trigger_handle_trigger(
			[
				'trigger'    => $this->trigger,
				'wp_user_id' => $user_id,
				'context'    => $context,
			]
		);
	}

}

CompleteSection::get_instance();
