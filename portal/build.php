<?php
require(dirname(__FILE__) . '/../configuration.php');

/**
* Local and encoding settings
*/
if(!isset($checkAuth))
  $checkAuth = true;

mb_language('uni');
mb_internal_encoding('UTF-8');
date_default_timezone_set('Europe/Istanbul');
setlocale(LC_MONETARY, 'tr_TR');
setlocale(LC_COLLATE, 'tr_TR');
setlocale(LC_ALL, 'tr_TR');
setlocale(LC_CTYPE, 'C');
Locale::setDefault('tr_TR'); // true

require(PATH . DS . 'system/vendor/autoload.php');

require(PATH . DS . 'portal/includes/class.sl.php');
require(PATH . DS . 'portal/includes/ICS.php');
require(PATH . DS . 'portal/includes/class.portal.php');
require(PATH . DS . 'portal/includes/class.mysql.php');
//require(PATH . DS . 'includes/ssp.class.php');
require(PATH . DS . 'portal/includes/PluploadHandler.php');

require(PATH . DS . 'portal/includes/plugins/HTMLMinify.smarty.php');
//require PATH . DS . 'system/vendor/phpmailer/phpmailer/PHPMailerAutoload.php';

$sl = new SL($settings);
$sl->session();
$sl->devicetype();
define('UP', '/uploads/');
define('TMP', UP. 'tmp/');

if(!$sl->auth(true) AND $checkAuth != false) {
  header("Location: /portal/login.php");
  exit;
}

$sl->debug('127.0.0.0'); // Local
$sl->debug('::1'); // Local
//$sl->debug('85.100.241.83'); // Local

//($sl->debug()?$sl->smarty->clearAllCache():'');
//($sl->debug()?$sl->nocache():'');

#$sl->smarty->clearAllCache();
// $err = (MODE == 'PRO' AND !$sl->debug())? 0 : E_ALL & ~E_NOTICE & E_WARNING;
// error_reporting(0);
$sl->smarty->assign('debug', $sl->debug());
/*
if($sl->debug())
  error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
*/
(@$sl->get['devmode'] == 1?$sl->smarty->assign('devmode','1'):'');

//error_reporting(E_ALL);

$portal = new Portal($sl);
/*
$forms = $sl->db->QueryArray("SELECT * FROM `forms`",MYSQLI_ASSOC);

for($i=0;$i<sizeof($forms);$i++){
  $json = $sl->decode(json_decode($forms[$i]['json'], true));

  $sl->db->Query("UPDATE `forms` SET `registerto`='{$json['registerto']}' WHERE `id` = {$forms[$i]['id']}");
}
exit;
*/
$modules = array();

$aksiyonlar = [
  1 => [1, "Randevu Verildi"],
  9 => [9, "Bilgi Verildi"],
  2 => [2, "Yanlış Numara"],
  3 => [3, "Ulaşılamadı"],
  4 => [4, "Müsait Değil, Tekrar Aranacak"],
  5 => [5, "İlgilenmiyor"],
  // [10, "Bad data"],
  // [11, "Negatif"],
  //         [5, 'Uygun Değil', [
  // [6,'İlgilendiği Bölüm Yok'],
  // [7,'Yabancı Uyruklu'],
  // [8,'Yatay Geçiş']
  //         ]]
];

switch($sl->user['group']) {
  case 3:
    $modules[] = array(
      /**
      * @param string $name
      * @param string $icon
      * @param string $file
      * @param string $uniq
      */
      'name' => 'Formlar',
      'icon' => 'mdi mdi-account-box',
      'file' => 'forms.php',
      'table' => 'forms',
      'uniq' => '93bae5c1ab5f8dee7d116bf6665be51f',

      /**
      * @param boolean $show
      */
      'show' => true,

      /**
      * @param string $table
      */
      'table' => 'forms',

      /**
      * @param array $subs
      */
    );
  break;
  default:
    $modules[] = array(
      /**
      * @param string $name
      * @param string $icon
      * @param string $file
      * @param string $uniq
      */
      'name' => 'Formlar',
      'icon' => 'mdi mdi-account-box',
      'file' => 'forms.php',
      'table' => 'forms',
      'uniq' => '93bae5c1ab5f8dee7d116bf6665be51f',

      /**
      * @param boolean $show
      */
      'show' => true,

      /**
      * @param string $table
      */
      'table' => 'forms',

      /**
      * @param array $subs
      */
    );

    $modules[] = array(
      'name' => 'İçerik Yönetimi',
      'desc' => 'Site tasarım ve içerik yönetim alanı',
      'icon' => 'mdi mdi-lan',
      'file' => 'contents.php',
      'uniq' => 'ff89f59e56dddc2b2a8a28a72fb3f420',
      'group' => '1',
      'show' => true,
      'table' => 'contents',
      'subs' => array(
        array('İçerikler','contents.php'),
        array('Yeni İçerik Ekle','content.sl.php?config=3'),
      ),
      'modules' => $sl->db->QueryArray("SELECT * FROM `content_modules` ORDER BY `default` ASC", MYSQLI_ASSOC, 'id')
    );
  break;
}

for($i=0;$i<sizeof($modules);$i++)
  $portal->addmodule($modules[$i]);
