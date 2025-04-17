<?php

function do_translation($str, $content) {
  global $sl;

  if (isset($content)) {
    return $sl->languages($content);
  }
}

function singlevalue($string,$table,$id,$function) {
  global $sl;
  //$val = explode(',',$param);
  /*
  if(strpos($string,'{')) {
    $string = explode('{',$string);
    $prefix = str_replace('}','',$string[1]);
    if($prefix == 'lang')
      $prefix = $lang['_prefix'];
    $string = $string[0];
  }

  $string = $db->QuerySingleValue("SELECT `".$lang['_prefix']."".$string."` FROM `".$table."` WHERE `id`='".$id."'");;

  switch ($function) {
    case 'upper':
      $string = mb_strtoupper($string, "UTF-8");
    break;

    default:
    break;
  }
  */
  return $string;
}

function SLucwords($string) {
  global $sl;

  return $sl->ucwords_tr($string);
}

function contents($str, $id) {
  global $sl;

  preg_match_all('/%(.*?)%/', $str, $matches);
  $content = $sl->db->QuerySingleRowArray("SELECT * FROM `contents` WHERE `id`='".$id."'", MYSQLI_ASSOC);
  $content = $sl->contents(array('id' => $id), false, 1, 0, false);

  $find = array(
    '{lang}'
  );

  $repl = array(
    "{{$sl->languages('prefix')}}"
  );

  $matches = $matches[1];

  $str = $sl->smarty->fetch('string:' . $str);

  for($i=0;$i<sizeof($matches);$i++) {
    $db_match = str_replace(
      $find,
      $repl,

      $matches[$i]
    );

    if(strpos($matches[$i], 'slug') !== FALSE) {
      $slug = $sl->slug($content);
      $str = str_replace("%{$matches[$i]}%", $slug, $str);
    } else {
      if(is_array($content[$db_match]))
        $str = str_replace("%{$matches[$i]}%", $content[$db_match][0], $str);

      if(isset($content[$db_match]))
        $str = str_replace("%{$matches[$i]}%", $content[$db_match], $str);
    }
  }

  $str = str_replace('%prefix%', $sl->languages('prefix'), $str);
  $str = str_replace('%theme_url%', $sl->settings['theme_url'], $str);

  return $str;
}

function formId($str) {
  return str_replace(
    array('[',']'),
    array('_','_'),
    $str
  );
}

// register with smarty
$this->smarty->registerPlugin("modifier","contents", "contents");
$this->smarty->registerPlugin("modifier","formId", "formId");
$this->smarty->registerPlugin("modifier","SLucwords", "SLucwords");
$this->smarty->registerPlugin("block", "tr", "do_translation");
