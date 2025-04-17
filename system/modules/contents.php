<?php

namespace master {
  use PDO;

  class content extends Core {
    private function form($response = false) {
      $data = $this->core->post;

      $recaptcha = new \ReCaptcha\ReCaptcha($this->core->settings['gcaptchaV2']['secret']);
      $resp = $recaptcha
                        ->verify($this->core->post['g-recaptcha-response'], $this->core->IP()['IPADDR']);

      $json = array('status' => 2, 'msg' => 'Bir hata oluştu, lütfen tekrar deneyiniz.');

      if(!$resp->isSuccess()) {
        $json = array('status' => 2, 'msg' => 'Doğrulama yapılamadı, lütfen tekrar deneyiniz.');
        // die($this->core->jsonencode($response));
      } else if (
        // !empty($data['fullname']) and
        // (!empty($data['phone']) or
        // !empty($data['email']))
        true
      ) {
        $form = $this->core->jsonencode($data);
        $timestamp = time();
        $timestampplus = strtotime('+1 minutes');
        $q = $this->core->Query(
          "INSERT INTO {$this->core->settings['dbprefix']}forms (`title`,`email`,`variables`,`json`,`ipaddress`,`status`,`type`,`published`)
          VALUES (?,?,?,?,?,?,?,?)",
          [$data['fullname'], $data['email'], $form, $form, $this->core->IP()['IPLONG'], 1, $this->content_found['id'], time()]
        );

		//         if ($this->core->QuerySingleValue("SELECT COUNT(*) FROM {$this->core->settings['dbprefix']}forms
		//           WHERE
		//             published < {$timestampplus} AND
		//             ipaddress = ?
		//         ", [$this->core->IP()['IPLONG']])
		// ) {
		//           $json = ((array(
		//             'status' => 1,
		//             'msg' => 'Mesajınız daha önce kayıt edilmiştir, lütfen daha sonra tekrar deneyiniz.'
		//           )));
		//         } else {
		//         }
      }

      if ($q) {
        $json = array(
          'status' => 0
        );
      }
      // } else {
      //   $json = array(
      //     'status' => 1,
      //     'msg' => 'Bir hata oluştu, mesajınız kayıt edilemedi lütfen tekrar deneyiniz.'
      //   );
      // }

      if($response)
        return $json;
      else
        die($this->core->jsonencode($json));
    }

    function contacts() {
      $sub = $this->content->get(
        [
          'where' => ['id' => 2492], 'columns' => 'name{},slug{},content{},sl_module,sl_template',
          'nodes' => true,
        ]
      );
      $sub['subs'] = $this->content->get(['multiple' => true, 'nodes' => true, 'columns' => 'name{},slug{},content{},sl_module,sl_template',
        'where' => ['parent' => $sub['id']]]);

      $sub['subs'] = array_map(function($e) {
        $e['contacts'] =
        $this->content->get(
            [
              'multiple' => true, 'nodes' => true, 'columns' => 'id,name{}',
            'where_nodes' => ['linked_pages' => [ $e['id'] ]]
            ]
          );
          return $e;
        },
        $sub['subs']
      );

      $this->core->view->append('sub', $sub);
      $contact = $this->core->view->fetch($sub['sl_template'] ? "templates/{$sub['sl_template']}" : 'page.fetch');

      $this->core->view->append('contact', $contact);
    }

    private function default() {
      if (empty($this->core->requests[0]) OR $this->core->requests[0] == $this->prefix) {
        $content = $this->content->getContent(['type' => 'default']);
        $path = 'templates/';

        $sliders = $this->content->get(['where' => ['sl_module' => 21], 'nodes' => true, 'multiple' => true, 'meta' => false, 'columns' => 'name{}']);

        // $news = $this->content->get(['limit' => 5, 'where' => ['sl_module' => 23, 'default' => 0], 'nodes' => true, 'multiple' => true, 'meta' => false, 'columns' => 'slug{},name{}']);
        // $team = $this->content->get(['limit' => 15, 'order' => ['id' => 'RAND(id)'], 'where' => ['sl_module' => 25], 'nodes' => true, 'multiple' => true, 'meta' => false, 'columns' => 'slug{},name{}']);
        $programs = $this->content->get(['where' => ['sl_module' => 27], 'nodes' => true, 'multiple' => true, 'meta' => false, 'columns' => 'slug{},name{},content{}']);
        $faq = $this->content->get(['where' => ['id' => 2667], 'nodes' => true, 'multiple' => false, 'meta' => false, 'columns' => 'slug{},name{},content{}']);

        if($this->core->requests[0] == 'tr') {
          $content['meta']['canonical'] = $this->core->settings['domain']['url'];
        } else {
          $content['meta']['canonical'] = $this->core->settings['domain']['url'] . '/tr';
        }

        // print_r($programs);
        // $programs = $this->renderPrograms($programs);
        $faq['nodes']['accordions']['text'] = $this->core->decode($faq['nodes']['accordions']['text']);
        $faq['nodes']['accordions']['name'] = $this->core->decode($faq['nodes']['accordions']['name']);

        $this->core->view->append('faq', $faq);
        $this->core->view->append('programs', $programs);
        $this->core->view->append('sliders', $sliders);
        $this->core->view->append('meta', $content['meta']);

        $this->core->view->output($path . $content['sl_template'], $content['id']);
      } else {
        switch ($this->core->removeEX(end($this->core->requests))) {
          default:
            $this->content->notfound();
        }
      }
    }

    public function content() {
      $where = [];
      $request = $this->content->getRequest();

      if(is_numeric($request['id']))
        $where["id"] = $request['id'];

      if(empty($where))
        $this->content->notfound();

      $this->content_found = $content = $this->content->get(['where' => $where, 'nodes' => true,
        'multiple' => false, 'images' => true, 'meta' => true,
        'columns' => 'sl_module,slug{},name{},content{},sl_template,parent']);

      $this->content_found = $content;

      if($request['slug'] != $content['slug']) {
        // $this->notfound();
        // return;
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: /{$this->prefix}/{$content['slug']}-{$content['id']}");
        exit;
      }

      if(
        empty($content)
        OR (isset($content['nodes']['link_clickable']) AND $content['nodes']['link_clickable'] == 2)
      ) {
        $this->content->notfound();
        return;
      }

      if($this->core->get['form'] == 'do') {
        $this->form();

        return;
      }

      $root = $content;

      if(
        $root['nodes']['single_page'] == 1
        AND $root['id'] != $content['id']
      ) {
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: /{$this->prefix}/{$root['slug']}-{$root['id']}");
        exit;
      }

      if($root['parent'] != 0 AND $root['nodes']['root'] != 1) {
        $parent = $root['parent'];

        for(;;) {
          $_ = $this->content->get(['where' => ['id' => $parent], 'nodes_columns' => 'video_desktop,video_mobile,single_page,root,theme,contentmenu,sl_order', 'nodes' => true,
          'multiple' => false, 'images' => true, 'meta' => true, 'columns' => 'sl_module,slug{},name{},sl_template,parent']);

          if($_['parent'] == 0 OR (isset($_['nodes']['root']) AND $_['nodes']['root'] == 1) ) {
            $root = $_;
            break;
          }

          $parent = $_['parent'];
        }
      }

      $this->meta($content['meta'] ?? []);

      $module = $this->content->getModule($content['sl_module']);

      if(!empty($module['file'])) {
        include(MODULES . $module['file']);
      }

      if(isset($content['nodes']['parameters']))
        $content['nodes']['parameters'] = $this->content->mergeKeys($content['nodes']['parameters']);

      $this->core->view->append('content', $content);
      $this->core->view->append('root', $root);

      if(isset($root['nodes']['contentmenu']) AND $root['nodes']['contentmenu'] == 1) {
        $root['subs'] = $this->content->get([
          'order' => [ ['id' => "{$root['nodes']['sl_order']}", 'type' => 'FIELD'] ],
          'columns' => 'name{},slug{},content{},sl_module,sl_template',
          'images' => true,
          'nodes' => true,
          'multiple' => true,
          'where' => ['parent' => $root['id']]
        ]);

        // if(empty($content['subs'])) {
        //   $content['subs'] = $this->content->get([
        //     'order' => [ ['id' => "{$content['nodes']['sl_order']}", 'type' => 'FIELD'] ],
        //     'columns' => 'name{},slug{},content{},sl_module,sl_template',
        //     'images' => true,
        //     'nodes' => true,
        //     'multiple' => true,
        //     'where' => ['parent' => $content['parent']]
        //   ]);
        // }

        if(!empty($root['subs']) AND $root['nodes']['single_page'] == 1) {
          $root['single_pages'] = [];

          for($i=0;$i<sizeof($root['subs']);$i++) {
            $sub = $root['subs'][$i];

            $root['subs'][$i]['subs'] = $sub['subs'] = $this->content->get(
              [
                'orderby' => [ 'id' =>  ["value" => "{$root['subs'][$i]['nodes']['sl_order']}", 'type' => 'FIELD'] ],
                'multiple' => true, 'images' => true, 'nodes' => true, 'columns' => 'id,name{},slug{},sl_module',
              'where' => ['parent' => $sub['id']]]);

            // $sub['subs'] = array_map(function($e) {
            //   $e['contacts'] =
            //   $this->content->get(
            //       [
            //         'multiple' => true, 'nodes' => true, 'columns' => 'id,name{}',
            //         'where' => ['linked_pages' => [$e['id']]]
            //       ]
            //     );
            //     return $e;
            //   },
            //   $sub['subs']
            // );

            $module = $this->content->getModule($sub['sl_module']);

            if(!empty($module['file'])) {
              include(MODULES . $module['file']);
            }

            $sub = $this->content->render($sub);
            $this->core->view->append('sub', $sub);

            $root['single_pages'][] = $this->core->view->fetch($sub['sl_template'] ? "templates/{$sub['sl_template']}" : 'page.fetch');

            if(!empty($sub['subs'])) {
              for($j=0;$j<sizeof($sub['subs']);$j++) {
                $_sub = $this->content->render($sub['subs'][$j]);
                $this->core->view->append('sub', $_sub);

                $root['single_pages'][] = $this->core->view->fetch($sub['sl_template'] ? "templates/{$sub['sl_template']}" : 'page.fetch');
              }
            }
          }
        }
      }

      // print_r($root);

      $this->core->view->append('form', []);

      if(
        isset($this->core->get['form'])
      ) {
        $response = $this->form(true);

        if($this->core->get['xhr']) {
          die(json_encode($response));
        }

        $this->core->view->append('form', $response);
      }

      $page = (($content['nodes']['root'] == 1) ? $root : $content);
      $page = $this->content->render($page);
      $tpl = !empty($content['sl_template']) ? "templates/{$content['sl_template']}" : 'contents.tpl';
      $this->core->view->append('root', $root);
      $this->core->view->append('content', $page);

      $this->core->view->output($tpl);
    }

    private function meta($meta = []) {
      $meta = $meta ?? [];

      $meta_defaults = [
        'title' => '',
        'desc' => '',
        'canonical' => '',
        'keywords' => ''
      ];

      $meta = array_merge($meta_defaults, $meta);
      $this->core->view->append('meta', $meta);
    }

    public function vcard() {
      $contact = $this->content->get(['columns' => 'name{}', 'nodes' => true,
        'where' => ["slug{{$this->core->prefix}}" => $this->core->requests[2], 'sl_module' => 25]]);
      $image = "{$this->core->settings['view_config']['template_path']}/assets/img/no.img.jpg";
      $image = (!empty($contact['nodes']['photo'][0])) ? PATH . $contact['nodes']['photo'][0] : $image;

      if(!$contact) {
        die('Oops!');
      }

      header('Content-Type: text/x-vcard');
      header('Content-Disposition: inline; filename= "'.$contact['name'].'.vcf"');

      if($image != "") {
        $getPhoto               = file_get_contents($image);
        $b64vcard               = base64_encode($getPhoto);
        $b64mline               = chunk_split($b64vcard,74,"\n");
        $b64final               = preg_replace('/(.+)/', ' $1', $b64mline);
        $photo                  = $b64final;
      }

      $vCard = "BEGIN:VCARD\r\n";
      $vCard .= "VERSION:3.0\r\n";
      $vCard .= "FN:{$contact['name']}\r\n";
      $vCard .= "TITLE:{$contact['nodes']['user_title']} - İstanbul TTO\r\n";

      if($contact['nodes']['email']) {
        $vCard .= "EMAIL;TYPE=internet,pref:{$contact['nodes']['email']}\r\n";
      }

      if($getPhoto){
        $vCard .= "PHOTO;ENCODING=b;TYPE=JPEG:";
        $vCard .= $photo . "\r\n";
      }

      if($contact['nodes']['phone']){
        $vCard .= "TEL;TYPE=work,voice:{$contact['nodes']['phone']}\r\n";
      }

      $vCard .= "END:VCARD\r\n";
      echo $vCard;
    }

    public function __construct() {
      $this->core = Core::getInstance();
      $this->content = new Contents;

      $this->content->menuInit();
      $this->core->view->append('menu', $this->content->menu);
      $this->prefix = $this->core->getLanguagePrefix();
      $this->content->statics();

      if(
        !isset($this->core->requests[0]) OR
        (isset($this->core->request[0]) AND $this->core->request[0] == $this->prefix)
      ) {
        $this->default();
        return;
      }

      switch ($this->core->requests[0]) {
        case ($this->core->requests[0] == 'mobile-mobile'):
          $this->core->view->append('img','mobile.jpg');
          $this->core->view->output('demo.mobile.tpl');
          exit;
        break;

        case ($this->core->requests[0] == 'demo'):
          $this->core->view->append('img','dentistun-new-header.jpg');
          $this->core->view->output('demo.tpl');
          exit;
        break;
        case (isset($this->core->requests[1]) AND $this->core->requests[1] == 'vcard'):
          // $this->vcard();
        break;
        case (isset($this->core->requests[1]) AND $this->core->requests[1] == 'form'):
          $this->form();
        break;
        case isset($this->core->requests[1]):
          $this->content();
        break;

        case (isset($this->core->requests[1]) AND preg_match('/(.*)-n\d*/', $this->core->requests[1])):
          //$this->news($this->core->requests[1]);
        break;

        default:
          $this->default();
        break;
      }
    }
  }

  new content();
}
