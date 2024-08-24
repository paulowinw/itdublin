<?php

namespace PrestoPlayer\Database;

class Table {

	/**
	 * Create a database table
	 *
	 * @param string  $name
	 * @param string  $columns
	 * @param integer $version
	 * @param array   $opts
	 * @return void
	 */
	public function create( $name, $columns, $version = 1, $opts = array() ) {
		$current_version = get_option( "{$name}_database_version", 0 );

		if ( $version == $current_version ) {
			return;
		}

		global $wpdb;

		$full_table_name = $wpdb->prefix . $name;

		$opts = wp_parse_args(
			$opts,
			array(
				'upgrade_method' => 'dbDelta',
				'table_options'  => '',
			)
		);

		$charset_collate = '';
		if ( $wpdb->has_cap( 'collation' ) ) {
			if ( ! empty( $wpdb->charset ) ) {
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			}
			if ( ! empty( $wpdb->collate ) ) {
				$charset_collate .= " COLLATE $wpdb->collate";
			}
		}

		$table_options = $charset_collate . ' ' . $opts['table_options'];

		// use dbDelta by default
		if ( 'dbDelta' == $opts['upgrade_method'] ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( "CREATE TABLE $full_table_name ( $columns ) $table_options" );
			update_option( "{$name}_database_version", $version );
			return;
		}

		if ( 'delete_first' == $opts['upgrade_method'] ) {
			$wpdb->query( "DROP TABLE IF EXISTS $full_table_name;" );
		}

		$wpdb->query( "CREATE TABLE IF NOT EXISTS $full_table_name ( $columns ) $table_options;" );

		update_option( "{$name}_database_version", $version );
	}

	/**
	 * Drops the table and database option
	 *
	 * @param string $name
	 * @return void
	 */
	public function drop( $name ) {
		global $wpdb;
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $name );
		delete_option( "presto_courses_{$name}_database_version" );
	}

	public function exists( $name ) {
		global $wpdb;
		$table_name = $wpdb->prefix . $name;
		$query      = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) );
		if ( $wpdb->get_var( $query ) == $table_name ) {
			return true;
		}
		return false;
	}
}
