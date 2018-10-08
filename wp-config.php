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
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'cripto_db');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'M|4YYUd<?1tR,LA7A?9~=0xl@hcYfnd6-~KcV-.W)Ea$:V<5@c`Qk ^!CmTrD4AY');
define('SECURE_AUTH_KEY',  '4K[~|JE16muR 0{,$@52DQdobY.#RQyP-6R0nv<5^2Sj47Vd1L+1Co`8IJ, _cZ0');
define('LOGGED_IN_KEY',    'tv.gp3ICw+=&WDF}9gxpjUDL|C5:Pi?!p]PR.g#Kw${,10%u|`O9g<<Jm( }f!~k');
define('NONCE_KEY',        '<P/N3ily25PZ><njOx<9Y1<&B|tvv(/{X]sS3@MBty4NC_y)vP8W$%~%xBm&j8=h');
define('AUTH_SALT',        'yXu$_)gz%(v+apT~K@{QnA7lnpsVeJf-a_|3<iAt%RORA+w^0<M!AtypIjU^(iP<');
define('SECURE_AUTH_SALT', '+XT 2ZkWaPSOs@.uHLqrmz443`R42`9*egZs3b39=(_E5u;mquI.T0pBwi=Q6J17');
define('LOGGED_IN_SALT',   'sr5CMt(8?7w#PR*_0_cy?}f>ce$NzW*b8Tc&Tc!|J:WF@K/U91xYV$,qpV&9/lqM');
define('NONCE_SALT',       'zxJo0|? 7Bb$ky^l-e|Bb%B>W j_~tf`O=xbQbOmW[tkHEHRng&*YRUGzdO3gG<K');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
