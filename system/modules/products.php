<?php

if(empty($content['sl_template']))
$tpl = 'products.html';

$products = 
$this->content->get(
  [
    'lazy' => ['content'],
    'where' => ['parent' =>  $content['id']],
    'nodes' => true, 'multiple' => true, 'meta' => false, 'nodes_columns' => 'page_image', 'columns' => 'content{},name{},slug{}'
  ]);

$this->core->view->append('products', $products);