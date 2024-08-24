<?php
/**
 * SetAdStatus.
 * php version 5.6
 *
 * @category SetAdStatus
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\AdvancedAds\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;
use DateTimeImmutable;

/**
 * SetAdStatus
 *
 * @category SetAdStatus
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class SetAdStatus extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'AdvancedAds';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'set_ad_status';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Set Ad Status', 'suretriggers' ),
			'action'   => $this->action,
			'function' => [ $this, 'action_listener' ],
		];

		return $actions;
	}

	/**
	 * Action listener.
	 *
	 * @param int   $user_id user_id.
	 * @param int   $automation_id automation_id.
	 * @param array $fields fields.
	 * @param array $selected_options selectedOptions.
	 * @throws Exception Exception.
	 *
	 * @return bool|array|void 
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		$ad_id = $selected_options['ad_id'];

		$ad_status = $selected_options['ad_status'];

		switch ( $ad_status ) {
			case 'publish':
				wp_publish_post( $ad_id );
				break;
			case 'advanced_ads_expired':
				$ad_options      = get_post_meta( $ad_id, 'advanced_ads_ad_options', true );
				$new_expiry_date = (int) strtotime( current_time( 'Y-m-d H:i:s' ) );
				if ( is_array( $ad_options ) ) {
					$ad_options['expiry_date'] = $new_expiry_date;
				}
				update_post_meta( $ad_id, 'advanced_ads_ad_options', $ad_options );
				$key = 'advanced_ads_expiration_time';
				update_post_meta( $ad_id, $key, gmdate( 'Y-m-d H:i:s', $new_expiry_date ) );
				break;
			default:
				wp_update_post(
					[
						'ID'          => $ad_id,
						'post_status' => $ad_status,
					]
				);
				break;
		}
		clean_post_cache( $ad_id );

		return WordPress::get_post_context( $ad_id );
	}
}

SetAdStatus::get_instance();
