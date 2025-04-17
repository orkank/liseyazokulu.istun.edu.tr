<?php
$checkAuth = false;
require('./build.php');

if(isset($sl->get['logout'])) {
  $sl->logout();

  header("Location: ".$sl->settings['portal_url']);
  exit;
}

if(isset($sl->get['auth'])) {
  if(
    $sl->PHPSESSID()
  ) {
  } else {
    $sl->alert(array($sl::ALERT_WARNING, ('PHPSESSID')));
  }

  $sl->login($sl->post['email'], $sl->post['password']);
  /*
  (!empty($sl->post['redirectURI']))?
    header("Location: ".rawurldecode($sl->post['redirectURI'])):
    */
  header("Location: ".$sl->settings['portal_url']);

  exit;
}

header("Location: portal.php");
exit;
?>
