<?php
/**
 * WPLoyaltyAddPointsCustomer.
 * php version 5.6
 *
 * @category WPLoyaltyAddPointsCustomer
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\WPLoyalty\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use Wlr\App\Helpers\EarnCampaign;

/**
 * WPLoyaltyAddPointsCustomer
 *
 * @category WPLoyaltyAddPointsCustomer
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class WPLoyaltyAddPointsCustomer extends AutomateAction {


	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'WPLoyalty';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'wp_loyalty_add_points_customer';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {

		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Add Points to Customer', 'suretriggers' ),
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
	 * @return array
	 * @throws Exception Exception.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		global $wpdb;
		if ( ! class_exists( 'Wlr\App\Helpers\Base' ) || ! class_exists( '\Wlr\App\Models\EarnCampaign' ) || ! class_exists( 'Wlr\App\Helpers\EarnCampaign' ) ) {
			return [];
		}
		$email               = $selected_options['user_email'];
		$point               = $selected_options['points'];
		$action_type         = 'point_for_purchase';
		$earn_campaign_table = new \Wlr\App\Models\EarnCampaign();
		$campaign_list       = $earn_campaign_table->getCampaignByAction( $action_type );
		$campaign_type       = '';
		foreach ( $campaign_list as $campaign ) {
			$campaign_helper     = EarnCampaign::getInstance();
			$processing_campaign = $campaign_helper->getCampaign( $campaign );
			$campaign_type       = $processing_campaign->earn_campaign->campaign_type;
		}
		if ( '' != $campaign_type ) {
			$action_data   = [
				'user_email'    => $email,
				'campaign_type' => $campaign_type,
				'points'        => $point,
			];
			$earn_campaign = EarnCampaign::getInstance();
			$points        = $earn_campaign->addEarnCampaignPoint( $action_type, $point, '', $action_data );
			if ( $points ) {
				$base_helper     = new \Wlr\App\Helpers\Base();
				$user            = $base_helper->getPointUserByEmail( $email );
				$sql             = 'SELECT * FROM ' . $wpdb->prefix . 'wlr_expire_points 
			WHERE user_email = %s ORDER BY id DESC LIMIT 1';
			$results      = $wpdb->get_results( $wpdb->prepare( $sql, $email ), ARRAY_A );// @phpcs:ignore
				$context['user'] = $user;
				$expire_date     = $results[0]['expire_date'];
				$timestamp       = is_numeric( $expire_date ) ? (int) $expire_date : null;
				$date_format     = get_option( 'date_format' );
				if ( is_string( $date_format ) ) {
					$context['point_expiry_date'] = wp_date( $date_format, $timestamp );
				}
				return $context;
			} else {
				throw new Exception( 'Points not added.' );
			}
		} else {
			throw new Exception( 'Campaign Not Found.' );
		}
	}

}

WPLoyaltyAddPointsCustomer::get_instance();
