<?php
/**
 * RafflePress core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\RafflePress;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\RafflePress
 */
class RafflePress extends Integrations {

	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'RafflePress';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'RafflePress', 'suretriggers' );
		$this->description = __( 'Best WordPress Giveaway and Contest Plugin.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/rafflepress.svg';

		parent::__construct();
	}

	/**
	 * Get Giveaway context data.
	 *
	 * @param int $giveaway_id Giveaway ID.
	 *
	 * @return array
	 */
	public static function get_giveaway_context( $giveaway_id ) {

		$context = [];
		global $wpdb;

		$giveaway = $wpdb->get_row( $wpdb->prepare( "SELECT name,starts,ends,active FROM {$wpdb->prefix}rafflepress_giveaways WHERE id=%d ORDER BY name ASC", $giveaway_id ), ARRAY_A );

		if ( ! $giveaway ) {
			return $context;
		}

		$giveaway_total_entries = $wpdb->get_var( $wpdb->prepare( "SELECT count(id) FROM {$wpdb->prefix}rafflepress_entries WHERE giveaway_id = %d", $giveaway_id ) );
		$giveaway_total_users   = $wpdb->get_var( $wpdb->prepare( "SELECT count(id) FROM {$wpdb->prefix}rafflepress_contestants WHERE giveaway_id = %d", $giveaway_id ) );

		$format = is_string( get_option( 'date_format' ) ) ? get_option( 'date_format' ) : 'YYYY-MM-DD';
		
		$context['giveaway_id']         = $giveaway_id;
		$context['giveaway_title']      = $giveaway['name'];
		$context['giveaway_start_date'] = gmdate( $format, strtotime( $giveaway['starts'] ) );
		$context['giveaway_end_date']   = gmdate( $format, strtotime( $giveaway['ends'] ) );
		$context['giveaway_entries']    = $giveaway_total_entries;
		$context['giveaway_user_count'] = $giveaway_total_users;
		$context['giveaway_status']     = ( true === $giveaway['active'] ) ? 'Active' : 'Inactive';
		
		return $context;
	}

	/**
	 * Get contestant context data.
	 *
	 * @param int $contestant_id Contestant ID.
	 *
	 * @return array
	 */
	public static function get_contestant_context( $contestant_id ) {

		$context = [];
		global $wpdb;

		$contestant = $wpdb->get_row( $wpdb->prepare( "SELECT fname,lname,email,status FROM {$wpdb->prefix}rafflepress_contestants WHERE id=%d", $contestant_id ), ARRAY_A );

		if ( ! $contestant ) {
			return $context;
		}

		$context['contestant_id']             = $contestant_id;
		$context['contestant_name']           = $contestant['fname'] . ' ' . $contestant['lname'];
		$context['contestant_email']          = $contestant['email'];
		$context['contestant_email_verified'] = ( 'confirmed' === $contestant['status'] ) ? 'Yes' : 'No';

		return $context;
	}

	/**
	 * Get full context data.
	 *
	 * @param array $data Giveaway Data.
	 *
	 * @return array
	 */
	public static function get_full_context( $data ) {

		$context = [];

		if (
			empty( $data ) ||
			! isset( $data['giveaway_id'] ) ||
			! isset( $data['contestant_id'] ) ||
			! isset( $data['entry_option_meta'] )
		) {
			return $context;
		}

		$context = array_merge(
			self::get_giveaway_context( $data['giveaway_id'] ),
			self::get_contestant_context( $data['contestant_id'] )
		);

		if ( empty( $context ) ) {
			return $context;
		}

		$context['performed_action_id']   = isset( $data['entry_option_meta']->id ) ? $data['entry_option_meta']->id : 0;
		$context['performed_action_name'] = isset( $data['entry_option_meta']->name ) ? $data['entry_option_meta']->name : '';

		return $context;
	}

	/**
	 * Is Plugin depended on plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return defined( 'RAFFLEPRESS_BUILD' ) || defined( 'RAFFLEPRESS_PRO_BUILD' );
	}
}

IntegrationsController::register( RafflePress::class );
