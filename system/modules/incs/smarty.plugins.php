<?php

function do_translation($str, $content) {
  global $sl;

  if (isset($content)) {
    return $sl->languages($content);
  }
}

function _contents($str, $id) {
  $this->engine->fetch('string:' . $str);
  return $str;
}

function getSVG($file) {
  $file = PATH .DS. '/uploads/' . str_replace(array('../', '/uploads/', '..'), '', $file);
  $find = array('xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1"','<?xml version="1.0" encoding="UTF-8"?><!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">');
  $replace = array('','');

  return (is_file($file)) ? str_replace($find, $replace, file_get_contents($file)) : '';
}

function renderTpl($params, $content, $engine, $tpl) {
  $core = \master\core::getInstance();
  $prefix = $core->getLanguagePrefix();

  $find = ['{','}'];
  $repl = ['{%','%}'];

  $content = (is_null($content)) ? '' : $content;
  $content = str_replace($find,$repl,$content);

  $contents = new \master\Contents;

  $render = $contents->get(['multiple' => false, 'columns' => $params['columns'], 'nodes' => true, 'where' => ['id' => $params['id']]]);

  if(empty($render))
    return '';

  $render['parameters'] = $contents->mergeKeys($render['nodes']['parameters']);

  $engine->assign('render', $render);

  return $engine->fetch("string: {$content}");
}

$this->engine->registerPlugin("block", "renderTpl", "renderTpl");

$this->engine->registerPlugin("modifier","getSVG", "getSVG");
$this->engine->registerPlugin("modifier","contents", "_contents");
$this->engine->registerPlugin("block", "tr", "do_translation");
