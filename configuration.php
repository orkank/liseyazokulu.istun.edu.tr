<?php
// SETTINGS
/**
 * Define PB as absolute document root
 * Define DS as directory separator
 */
define('PATH', dirname(__FILE__));
define('PB', dirname(__FILE__));
define('UP', '/uploads/');
define('DS', DIRECTORY_SEPARATOR);
define('SYSTEM', PATH . DS . 'system' . DS);
define('MODULES', PATH . DS . 'system' . DS . 'modules' . DS);
define('MODULES_INC', PATH . DS . 'system' . DS . 'modules' . DS . 'incs' . DS);

define('SALT', 'I)m1mKQNYvm^U)j^CXYtVHQTmryiNvL9TNuwx(@0X2Rejavz4Ot4jvdi1V');
define('COST', '12');
define('SL',true);

/**
 *  @param string $mode
 *  dev: development settings
 *  pro: production settings
 */

 // Settings
$settings = array(
  'PATH' => PATH,

  // 'smtp' => array(
  //   'username' => 'orkan@koylu.net',
  //   'password' => '00697e71-52a7-4c0d-a304-5742820d6908',
  //   'secure' => false,
  //   'port' => '2525',
  //   'server' => 'smtp.elasticemail.com',
  //   'sender_email' => 'orkan@koylu.net',
  //   'sender_name' => 'Orkan Köylü',
  //   'debug' => 0
  // ),
  /**
  * SMTP settings
  * istun.universitesi
  * Sst_*2040505_?1
  */
  'smtp' => array(
    'username' => 'noreply@istun.edu.tr',
    'password' => '12istnForce12_*',
    'secure' => 'tls',
    'port' => '25',
    'server' => 'smtp.office365.com',
    'sender_email' => 'noreply@istun.edu.tr',
    'form' => array(
        'name' => 'İSTÜN Web',
      'email' => 'noreply@istun.edu.tr',
    ),
    'sender_name' => 'İSTÜN Web',
    'reply_name' => 'İSTÜN Web',
    'reply_email' => 'lisekisokulu@istun.edu.tr',
    'charset' => 'utf-8',
    'debug' => 0,
  ),

  'database' => array(
    'db' => 'liseyazokulu',
    'user' => 'liseyazokulu',
    'password' => 'Ozdc531!9',
    'host' => 'localhost',
    'port' => '3306',
    'prefix' => ''
  ),

  'times' => array(
    'full' => 'Y-m-d H:i:s',
  ),

  'gcaptchaV3' => array(
    'secret' => '6LdRq6kZAAAAAMieqafWYGYrEYAvYrCcxQ3L-dtE',
    'key' => '6LdRq6kZAAAAAGqWl953Rk2fM7Cz4KWeSe9j1aC_',
    'verify_url' => 'https://www.google.com/recaptcha/api/siteverify',
  ),

  'gcaptchaV2' => array(
    'secret' => '6LcMAVMgAAAAANga26zgkEHm02uAVvXH8K20pCzk',
    'key' => '6LcMAVMgAAAAAHZtuDQgdgZgBKNEPG00ZU5--tN9',
  ),

  /**
   *  @param string $url
   *  Full domain URL, end with /
   */
  'domain' => array(
    'url' => 'https://liseyazokulu.istun.edu.tr',
    'static' => 'https://liseyazokulu.istun.edu.tr',
    'host' => 'liseyazokulu.istun.edu.tr'
  ),

  /**
   * Theme absolute path, end with /
   */
  'templates' => array(
    'path' => PATH . '/templates/',
    'theme' => 'default',
    'default' => 'system'
  ),
  /**
   * Cache absolute path, must be 0755 chmod
   */
  'cache' => array(
    'path' => PATH . '/cache/',
    'expire' => '+1 minute',
    'driver' => 'Files',
    'enabled' => true
  ),

  'portal_url' => '/portal/',
  'portal_theme_path' => PB . '/portal/templates/',
  'upload' => UP,

  /**
   * SESSION SETTINGS
   */

  'cookie' => array(
    'session' => 'SLSESS',
    'name' => 'IDENTITY',
    'lifetime' => (90 * 24 * 60 * 60),
    'secure' => true,
    'httpOnly' => true,
    'samesite' => 'Lax', // None || Lax || Strict
    'path' => '/'
  ),

  'log' => array(
    'enabled' => true,
    'path' => PATH . DS . 'logs' . DS
  ),

  'allowCors' => array(
  )
);

if(
  $_SERVER['REMOTE_ADDR'] == '::1' OR
  // $_SERVER['REMOTE_ADDR'] == '127.0.0.1' OR
  $_SERVER['REMOTE_ADDR'] == '192.168.50.42'
) {
  // echo '<!-- ALERT: local settings applied! -->';

  define('MODE', 'DEV');
  define('DEBUG_SPEED', false);

  $settings['MODE'] = MODE;

  $settings['domain'] = array(
    'url' => 'https://dev.liseyazokulu.istun.edu.tr',
    'static' => 'https://dev.liseyazokulu.istun.edu.tr',
    'host' => 'dev.liseyazokulu.istun.edu.tr'
  );

  $settings['database'] =
  array(
    'db' => 'liseyazokulu',
    'user' => 'root',
    'password' => '12Orkan12',
    'host' => 'localhost',
    'port' => '3306',
    'prefix' => ''
  );
} else {
  define('MODE', 'PRO');
  define('DEBUG_SPEED', false);

  $settings['MODE'] = MODE;
}

// if(
//   $_SERVER['REMOTE_ADDR'] == '::1'
// ) {
//   define('MODE', 'DEV');
//   define('DEBUG_SPEED', true);

//   $settings['MODE'] = MODE;
// }

if(
  $_SERVER['HTTP_HOST'] != $settings['domain']['host']
) {
  //header("Location: {$settings['domain']['url']}");
  die('DOMAIN');
}

define('COOKIE', $settings['cookie']['name']);

$settings['view_config'] = array(
  'templates' => $settings['templates'],
  'template_path' => $settings['templates']['path'] . $settings['templates']['theme'],
  'MODE' => MODE,
  'PATH' => PATH,
  'cache' => $settings['cache'],
  'domain' => $settings['domain'],
  'gcaptchaV2' => $settings['gcaptchaV2'],
  'asset_version' => '0.01',
  'theme' => $settings['domain']['static'] . '/templates/' . $settings['templates']['theme'],
  'asset' => $settings['domain']['static'] . '/templates/' . $settings['templates']['theme'] . '/assets/',
);

/**
 * Local and encoding settings
 */
mb_language('uni');
mb_internal_encoding('UTF-8');
date_default_timezone_set('Europe/Istanbul');
setlocale(LC_MONETARY, 'tr_TR');
setlocale(LC_COLLATE, 'tr_TR');
setlocale(LC_ALL, 'tr_TR');
setlocale(LC_CTYPE, 'C');
Locale::setDefault('tr_TR');
$err = (MODE == 'PRO') ? 0 : E_ALL & ~E_NOTICE & ~E_WARNING;
error_reporting($err);

ini_set('session.cookie_samesite', 'Strict');
ini_set('session.cookie_secure', '1');
ini_set('session.cookie_httponly', '1');

// END SETTINGS