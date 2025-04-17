<?php

namespace master {
  class news extends Core {

    private function statics() {
      $company = $this->content->get(['where' => ['id' => 2326], 'nodes' => true, 'multiple' => false, 'meta' => false, 'columns' => 'slug{},name{},content{}']);
      $company['nodes']['parameters'] = $this->content->mergeKeys($company['nodes']['parameters']);

      $this->core->view->append('company', $company);
    }

    public function getItems($limit = 0) {
      return $this->content->get(
        [
          'where' => [ 'sl_module' => 23, 'default' => '0', 'id' => ['value' => $this->request['id'], 'selector' => '!='] ],
          'limit' => $limit ?? false,
          'images' => true,
          'nodes' => true,
          'multiple' => true, 'meta' => false, 'columns' => 'slug{},name{}'
        ]
      );
    }

    public function init(int $id = 0): void {
      $page = $this->content->get(
        [
          'where' => ['id' => $this->request['id']],
          'images' => true,
          'nodes' => true,
          'multiple' => false, 'meta' => true, 'columns' => 'slug{},name{},content{},default,sl_template'
        ]
      );

      // $news_count = $this->core->QuerySingleValue("SELECT COUNT(*) FROM `contents` WHERE `sl_module` = 23 AND `default` = 0");

      $this->core->view->append('meta', $page['meta']);
      $this->core->view->append('page', $page);

      $this->core->view->output("templates/{$page['sl_template']}");
    }

    public function __construct() {
      $this->core = Core::getInstance();
      $this->content = new Contents;

      $this->prefix = $this->core->getLanguagePrefix();
      $this->request = $this->content->getRequest();

      $this->init();
      exit;
    }
  }

  new news();
}