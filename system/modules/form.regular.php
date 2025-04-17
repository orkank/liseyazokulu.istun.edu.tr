<?php

namespace master {
  class news extends Core {

    public function init(int $id = 0): void {
    }

    public function __construct() {
      $this->core = Core::getInstance();
      $this->content = new Contents;

      $this->prefix = $this->core->getLanguagePrefix();
      $this->request = $this->content->getRequest();
      $blog = $this->content->get(['where' => [ 'default' => 1, 'sl_module' => 23], 'nodes' => true, 'multiple' => false, 'meta' => false, 'columns' => 'slug{},name{}']);
      $this->core->view->append('blog', $blog);

      if(is_numeric($this->request['id']))
        $this->init();
      else
        $this->content->notfound();
    }
  }

  new news();
}