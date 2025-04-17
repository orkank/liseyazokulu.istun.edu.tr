<?php
define('PB', $_SERVER['DOCUMENT_ROOT']);
define('UP', '/uploads/');
define('TMP', UP. 'tmp/');
define('DS', DIRECTORY_SEPARATOR);

define('SALT',         'I)m1mKQNYvm^U3&0GTU)j^CXYtVHQTmryi9TNuwx(@0X2Rejavz4Ot4jvdi1V');
define('COST',         '12');

// GENERAL SETTINGS
$settings = array(
  /**
  *  @param string $mode
  *  dev: development settings
  *  pro: production settings
  */
  'mode' => 'pro',

  /**
  *  @param array $database
  *  pre: pre-production settings
  *  pro: production settings
  */
  'database' => array(
    'db' => '',
    'user' => '',
    'password' => '',
    'host' => '127.0.0.1',
    'port' => '3306',
    'prefix' => ''
  ),

  'gcaptchaV3' => array(
    'secret' => '-dtE',
    'key' => '',
    'verify_url' => 'https://www.google.com/recaptcha/api/siteverify',
  ),
  'gcaptchaV2' => array(
    'secret' => '6LdRq6kZAAAAAMieqafWYGYrEYAvYrCcxQ3L-dtE',
    'key' => '6LdRq6kZAAAAAMieqafWYGYrEYAvYrCcxQ3L-dtE',
  ),

  /**
  * Theme absolute path, end with /
  */
  'theme_path' => PB . '/templates/default/',
  'system_path' => PB . '/templates/system/',
  'portal_theme_path' => PB . '/portal/templates/',

  /**
  * Upload folder, must be 0777 chmod
  */
  'upload' => UP,
  'domain' => array(
    'url' => 'https://istanbultto.idangerous.net',
    'static' => 'https://istanbultto.idangerous.net',
    'host' => 'istanbultto.idangerous.net'
  ),

  /**
  * URL for portal path, end with /
  */
  'portal_url' => '/portal/',

  /**
  *
  */
  'session_timeout' => (10 * 365 * 24 * 60 * 60),
  'cookie_name' => 'IDENTITY',
  'cookie' => array(
    'session' => 'SLSESS',
    'name' => 'IDENTITY',
    'lifetime' => time() + (90 * 24 * 60 * 60),
    'secure' => true,
    'httpOnly' => true,
    'samesite' => 'Lax', // None || Lax || Strict
    'path' => '/'
  ),

  /**
  * SMTP settings
  */
  'smtp' => array(
      'username' => '',
      'password' => '',
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
	  'reply_email' => 'noreply@istun.edu.tr',
	  'charset' => 'utf-8',
	'debug' => 0,
  )
);

define('SL',true);

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
Locale::setDefault('tr_TR'); // true

require(PB . DS . 'system/vendor/autoload.php');

require(PB . DS . 'portal/includes/class.sl.php');
require(PB . DS . 'portal/includes/ICS.php');
require(PB . DS . 'portal/includes/class.portal.php');
require(PB . DS . 'portal/includes/class.mysql.php');
//require(PB . DS . 'includes/ssp.class.php');
require(PB . DS . 'portal/includes/PluploadHandler.php');

require(PB . DS . 'portal/includes/plugins/HTMLMinify.smarty.php');
//require PB . DS . 'system/vendor/phpmailer/phpmailer/PHPMailerAutoload.php';

$sl = new SL($settings);
$sl->session();
$sl->devicetype();

$sl->debug('127.0.0.0'); // Local
$sl->debug('::1'); // Local
//$sl->debug('85.100.241.83'); // Local

//($sl->debug()?$sl->smarty->clearAllCache():'');
//($sl->debug()?$sl->nocache():'');

#$sl->smarty->clearAllCache();
$err = ($settings['mode'] == 'pro' AND !$sl->debug())? 0 : E_ALL;
// error_reporting($err);
$sl->smarty->assign('debug', $sl->debug());

if($sl->debug())
  // error_reporting(E_ALL && ~E_NOTICE);

(@$sl->get['devmode'] == 1?$sl->smarty->assign('devmode','1'):'');

//error_reporting(E_ALL);