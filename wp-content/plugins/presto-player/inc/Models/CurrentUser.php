<?php

namespace PrestoPlayer\Models;

class CurrentUser extends \WP_User {

	public function __construct() {
		$user = wp_get_current_user();
		return parent::__construct( $user->ID );
	}

	public static function getUser() {
		return new self();
	}

	public static function getIP() {
		return isset( $_SERVER['REMOTE_ADDR'] ) ? filter_var( wp_unslash( $_SERVER['REMOTE_ADDR'] ), FILTER_VALIDATE_IP ) : null;
	}

	public static function canAccessVideo( $id ) {
		return (bool) apply_filters( 'presto-player-show-private-video', is_user_logged_in(), $id );
	}
}
