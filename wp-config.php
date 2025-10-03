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
define( 'DB_NAME', 'eia-wp' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

if ( !defined('WP_CLI') ) {
    define( 'WP_SITEURL', $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] );
    define( 'WP_HOME',    $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] );
}



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
define( 'AUTH_KEY',         '3bBTJ6G6WzVdwzhQdp4yh5717l114Ac3hsY7YWhdvDnyl3UdChU0XEKJJHiLPRqh' );
define( 'SECURE_AUTH_KEY',  '0UFuOq0wMhyYYR4Te8hTOfLUo8r7DyjsyzwS8WETVNVaMltmCo7g4S5akbLIU1oN' );
define( 'LOGGED_IN_KEY',    'dfsKIpNZQke52V8lmof3vBZzan77gQnWcEkCeWEAfagVtp7xGsTH49FVPpMzkiAA' );
define( 'NONCE_KEY',        'W1qu1Z0T300GeA6TYdQWhjAcrwafpotEwWqcDFIPD4yHVx0frnj7c1J8uHP30LUz' );
define( 'AUTH_SALT',        '93XY0KfW7bhPakK69Liwa9VZ6F66qfY8gCVxxI1khe0icNz9kI5LDLxh9z1qvLJE' );
define( 'SECURE_AUTH_SALT', 'KFhrSy0WVoUkT8G1C2uh7bvL4JW3MVPKN7h8YVZxYp2TjLgHlXT5QBYodtRRsSYx' );
define( 'LOGGED_IN_SALT',   'sNC8NizzUAIBWAjOrvl0tRR7xd9IvdGyz3feAXy7FFtcG2zCbKGOMwuA3GzGsamj' );
define( 'NONCE_SALT',       'qzHlUeKSj2FD9NOBSeDY2xVilMA3Q9SaDRQtnuFDnM56opUvvkDm2lvBPmfOXeQ8' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
