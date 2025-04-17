<?php

if(isset($sub)) {
  $sub['team'] =
    $this->content->get(['order' => [ ['id' => "{$sub['nodes']['sl_order']}", 'type' => 'FIELD'] ],
    'columns' => 'name{},slug{},content{}', 'nodes' => true,
    'multiple' => true, 'where' => ['parent' => $sub['id']]]);
  
  $sub['sl_template'] = 'team';
} else {
  $content['sl_template'] = 'team.member.html';
}

