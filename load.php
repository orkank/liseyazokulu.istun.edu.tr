<?php
//define('SL', true);

require('configuration.php');
require('modules.php');
require(SYSTEM . 'vendor/autoload.php');

use master\Core;

$core = new Core($settings, $modules);

$core->start();
