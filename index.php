<?php
require('load.php');
require $core->modules->file(isset($core->requests[1]) ? $core->requests[1] : false);

#echo "<!-- ".$core->getLoadTime()." -->";
