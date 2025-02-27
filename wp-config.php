<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'staging_realcaller' );

/** MySQL database username */
define( 'DB_USER', 'staging_realcaller' );

/** MySQL database password */
define( 'DB_PASSWORD', 'nGuGWK7l}51a' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'kqLaMFu;{k=rh.YZCP<HtD^9O2e|e4% wNM 2 m#jLSL)1oL-Vd9e`|T^._ZMO|Z' );
define( 'SECURE_AUTH_KEY',  '2c>voS{<4-?Nu!,wS6@4h!E8O3`Vc}-:I{JO2sHg1sqmWj];*qnQ3kqLyr_.=9!$' );
define( 'LOGGED_IN_KEY',    'Y>sv7g136#xf@&*9 z2ckEFCkF`%&2rPI3_q_d%jUz`4&t-n^2f9V%5bl|N4nlmy' );
define( 'NONCE_KEY',        ' QIBO#JL@V6Pm$9Ae]4!w[1E&MTA-0ms$JFiB|R{H/g>77OB`t3^)qRG[8:<cH5G' );
define( 'AUTH_SALT',        'Lz#KTY-%Ow1Y(i~xW4rOit Z<1m`zr4ly:n7pAP4n-Z_HsYqR1:H#y)>%U,X@e;H' );
define( 'SECURE_AUTH_SALT', '6}coK3:IxHba(dU1lR#DR%vcRO6H{?mZ-+Q$Vk9=#@[Gnv$[gLI3W[l>+1@e9<E+' );
define( 'LOGGED_IN_SALT',   ']ZI|c{y&_OxLj#q`:<<3cFjJO|#V$/Q]HXaw1 @Bs$.hzk&6UA5yM v%,;U!W4Fy' );
define( 'NONCE_SALT',       'c##YEw!.e~V,mL[ }zf vn83*WI>%O=whsZG$X%&msvq7uB{#eF&0{4^H~#=8hG>' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define('WP_DEBUG', false);
define('WP_DEBUG_LOG', false);

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

if ( ! defined( 'FS_METHOD' ) ) define( 'FS_METHOD', 'direct' );

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
