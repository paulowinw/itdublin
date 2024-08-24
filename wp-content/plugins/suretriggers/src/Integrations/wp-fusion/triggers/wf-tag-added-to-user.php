<?php
/**
 * WfTagAddedToUser.
 * php version 5.6
 *
 * @category WfTagAddedToUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\WPFusion\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

/**
 * WfTagAddedToUser
 *
 * @category WfTagAddedToUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class WfTagAddedToUser {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'WPFusion';

	/**
	 * Trigger name.
	 *
	 * @var string
	 */
	public $trigger = 'wf_tag_added_to_user';

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
			'label'         => __( 'Tag Added To User', 'suretriggers' ),
			'action'        => $this->trigger,
			'common_action' => 'wpf_tags_applied',
			'function'      => [ $this, 'trigger_listener' ],
			'priority'      => 10,
			'accepted_args' => 2,
		];

		return $triggers;
	}

	/**
	 * Trigger listener
	 *
	 * @param int   $user_id User ID.
	 * @param array $tags Tags.
	 *
	 * @return void
	 */
	public function trigger_listener( $user_id, $tags ) {

		if ( ! function_exists( 'wp_fusion' ) ) {
			return;
		}
		
		$context['user_id'] = WordPress::get_user_context( $user_id );
		foreach ( $tags as $tag ) {
			$context['fusion_tag'] = $tag;
		}
		$context['tags'] = wp_fusion()->user->get_tags( $user_id, true );
		
		AutomationController::sure_trigger_handle_trigger(
			[
				'trigger'    => $this->trigger,
				'wp_user_id' => $user_id,
				'context'    => $context,
			]
		);
	}
}

WfTagAddedToUser::get_instance();
