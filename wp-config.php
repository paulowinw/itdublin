<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wp_itdublin' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'C$$8~+e#`Y-z]!$tB&9MaDsdjk%^vJ9)>JVP5hFPUP,=[={Qo}Q}KRs{W@1fH}BL' );
define( 'SECURE_AUTH_KEY',  '=W0=b<L<fLO:;)<2f=V6~LH4A{PJ693xL<Atp6X)7]4u>AgbK)kFS@JsALi;jw~w' );
define( 'LOGGED_IN_KEY',    '3wXux__oOjpB^.ersze#4jZY8;Ya5o(4=>RzgsiZR![!hy}&X&}V*n6wX?4!xa$^' );
define( 'NONCE_KEY',        'RM^7yR6&A7x;3p44ZH;tQtvz0]kTDs5/{ MtZyre~ T7QJ2_BA)A;6lLvw4_%mA8' );
define( 'AUTH_SALT',        'Hi=><.ib}2/iCWHmr{t0F5.Yd{-z25DYdo4<x2=86XXmzyGj-[Fov+?rKhSvf>KA' );
define( 'SECURE_AUTH_SALT', '}pgH<5=eGuiL52(Z1PgVy=/wS9H/]scV6_r|<$#=R(.[@+ 4iMbtTf(I6.cg[PGO' );
define( 'LOGGED_IN_SALT',   '$J[#Quu~LNmyyCoGH+$y{0p]O$VWXpC.J[@t4V*U1ln/8i`)>#24up-.fuu]QRie' );
define( 'NONCE_SALT',       'e#<KGTK.QTM&bufxs;*JQ],KItxGg |21o%`AG6Z&+^f~TbA7n/{4v8Lwd/h;a&V' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
