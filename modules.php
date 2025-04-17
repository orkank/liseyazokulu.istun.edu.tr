<?php

/**
* To register module follow below parameters
*
* @var string $slug parameter url prefix after lang parameter
* Can be use regex
* NULL one is default
*
* @var path $file absolute path of module file
*
* @var boolean $default set true or false
* Default false
* ./composer.phar dumpautoload -o
**/

$modules = array();

$modules[] = array(
  'prefix' => NULL,
  'regex' => '',
  'file' => MODULES . 'contents.php'
);

$modules[] = array(
  'prefix' => 'xhr',
  'regex' => '',
  'file' => MODULES . 'xhr.php',
  'callback' => function($e) {
    return $e['file'];
  }
);

// $modules[] = array(
//   'prefix' => '',
//   'customprefix' => '',
//   'regex' => '/(.*)\-p\d/',
//   'file' => MODULES . 'product.php',

//   /*
//   'get' => function($e) {
//     return end(explode($e['customprefix'], $e));
//   },

//   'callback' => function($e) {
//     return strstr($e['request'], $e['customprefix'], '-p-') ? true : false;
//   }
//   */
// );
$modules[] = array(
  'prefix' => 'checkout',
  'regex' => '',
  'file' => MODULES . 'checkout.php',
  'callback' => function($e) {
    return $e['file'];
  }
);

$modules = new ArrayObject($modules);
