<?php

define('PROTOCOL', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://');
define('HOST', $_SERVER['HTTP_HOST']);
define('PREFIX', preg_replace(array('/\:/', '/\./'), '_', HOST).'_');
define('URL', PROTOCOL.HOST);

function getEnvOr($key, $default) {
	$value = getenv($key);
	return !empty($value) ? $value : $default;
}

if (getenv('CLEARDB_DATABASE_URL')): die('HEllo');
	$parts = parse_url($url);
	define('DB_NAME', substr($parts['path'], 1));
	define('DB_USER', $parts['user']);
	define('DB_PASSWORD', $parts['pass']);
	define('DB_HOST', $parts['host']);
else:
	define('DB_NAME', getEnvOr('DB_NAME', 'wordpress'));
	define('DB_USER', getEnvOr('DB_USER', 'root'));
	define('DB_PASSWORD', getEnvOr('DB_PASSWORD', ''));
	define('DB_HOST', getEnvOr('DB_HOST', 'localhost'));
endif;

define('DB_CHARSET', getEnvOr('DB_CHARSET', 'utf8mb4'));
define('DB_COLLATE', getEnvOr('DB_COLLATE', ''));

define('AUTH_KEY', getEnvOr('AUTH_KEY', 'uKa(B9 slREej&K-gsUKvl=2R457a9`eZp0_Ib %H+TChCC/0V)$@s&C{ga}`6J`'));
define('SECURE_AUTH_KEY', getEnvOr('SECURE_AUTH_KEY', 'd@B;=}pf]x$(B]&,LL?oL3Ap;wsJI}wfgzjf9c$:r+R}en$^b$Nt]:Sk  (FJO4$'));
define('LOGGED_IN_KEY', getEnvOr('LOGGED_IN_KEY', 'YnZyQMK{iWF=c7-uwhu4T<M4FrDT][#H|[=Z_u0vWSJ+s^;3=9hNKvng~WQ#Z6NK'));
define('NONCE_KEY', getEnvOr('NONCE_KEY', 'YDao1D3lMdp.Aecj-ZU(@#NG$xodpI6)Q%T286J|CwC5-kJy>O#T}By9VZ3Y@q ,'));
define('AUTH_SALT', getEnvOr('AUTH_SALT', 'SuvxGYsr91F~aVx2XK!BLS9?4q48ODPY#1=*^44Z:%q2Sw(1 }3#5v4NQ(P6GZ.N'));
define('SECURE_AUTH_SALT', getEnvOr('SECURE_AUTH_SALT', 'eZ!^(BS:P$R&d/7PA9=4266JGz3:kSFEHlq1DOv!5*/0r(xDY}jp8;r2qeXo*[QC'));
define('LOGGED_IN_SALT', getEnvOr('LOGGED_IN_SALT', '-/iPx?1Uz:flP)-T2:@UZ~}cW32BoM}S_p;gfqF.ZI*bk!W,Pdy.d@m$<X{o:s@3'));
define('NONCE_SALT', getEnvOr('NONCE_SALT', ']c5oeTN0OF{Tif5a7@h<GY#N-(X/P?&z@4JqdmkD(EU n&aO6=<Qfcn^f7nHf*9`'));

define('WP_SITEURL', getEnvOr('WP_SITEURL', URL));
define('WP_HOME', getEnvOr('WP_HOME', URL));

define('DISALLOW_FILE_EDIT', getEnvOr('DISALLOW_FILE_EDIT', true));
define('DISALLOW_FILE_MODS', getEnvOr('DISALLOW_FILE_MODS', true));
define('AUTOMATIC_UPDATER_DISABLED', getEnvOr('AUTOMATIC_UPDATER_DISABLED', true));
define('WP_DEFAULT_THEME', getEnvOr('WP_DEFAULT_THEME', 'theme'));

/**
 * File Settings
 *
 * See doc · https://deliciousbrains.com/wp-offload-media/doc/settings-constants/
 */
define('AS3CF_SETTINGS', serialize(array(
	'provider' => getEnvOr('FS_PROVIDER', 'aws'), // Storage Provider ('aws', 'do', 'gcp')
	'access-key-id' => getEnvOr('FS_KEY_ID', null), // Access Key ID for Storage Provider (aws and do only, replace '*')
	'secret-access-key' => getEnvOr('FS_ACCESS_KEY', null), // Secret Access Key
	'bucket' => getEnvOr('FS_BUCKET', 'mybucket'), // Bucket to upload files to
	'region' => getEnvOr('FS_BUCKET', ''), // Bucket region (e.g. 'us-west-1' - leave blank for default region)
	'copy-to-s3' => true, // Automatically copy files to bucket on upload
	'serve-from-s3' => true, // Rewrite file URLs to bucket
	'domain' => 'path', // Bucket URL format to use ('path', 'cloudfront')
	'enable-object-prefix' => true, // Enable object prefix, useful if you use your bucket for other files
	'object-prefix' => PREFIX.'/uploads/', // Object prefix to use if 'enable-object-prefix' is 'true'
	'use-yearmonth-folders' => true, // Organize bucket files into YYYY/MM directories
	'force-https' => false, // Serve files over HTTPS
	'remove-local-file' => false, // Remove the local file version once offloaded to bucket
	'object-versioning' => true // Append a timestamped folder to path of files offloaded to bucket
)));

define('WP_DEBUG', getEnvOr('DEBUG', false));

$table_prefix = PREFIX;

if (!defined('ABSPATH')):
	define('ABSPATH', dirname(__FILE__) . '/');
endif;

require_once(ABSPATH . 'wp-settings.php');

/** Options configurations
 *
 * Key => Value set of options to be applied
 */
global $wp_rewrite;

foreach (array(
	'permalink_structure' => '/%postname%/', // change the permalink structure
	// ...
) as $key => $value):
	update_option($key, $value);
endforeach;

$wp_rewrite->flush_rules();

/** Plugins activations
 *
 * Key => Value set of options to be applied
 */
include_once(ABSPATH . 'wp-admin/includes/plugin.php');

foreach (array(
	'amazon-s3-and-cloudfront/wordpress-s3.php'
) as $plugin):
	activate_plugin($plugin);
endforeach;
