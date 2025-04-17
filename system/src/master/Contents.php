<?php
namespace master {
	class Contents {

    public function __construct() {
			$this->core = \master\core::getInstance();
			$this->prefix = $this->core->getLanguagePrefix();
    }

		public $menu = [];

    public function str_contains($haystack, $needle) {
      return (strpos($haystack, $needle) !== false);
    }

    public function getCookiePrograms() {
      $programs = $_COOKIE['Programs'] ?? '';
      $programs = explode(',', $programs);

      $this->core->view->append('programs_ids', json_encode($programs) );

      return $programs;
    }

    public function render($page = []) {
      if(isset($page['content'])) {
        if($this->str_contains($page['content'], '%AKORDIYON%')) {
          $this->core->view->append('sub', $page);
          $rendered_tpl = $this->core->view->fetch('widgets/accordion.tpl');
          $page['content'] = str_replace('%AKORDIYON%', $rendered_tpl, $page['content']);
          $page['renders']['accordion'] = true;
        }

        if($this->str_contains($page['content'], '%GALERI%')) {
          $this->core->view->append('page', $page);
          $rendered_tpl = $this->core->view->fetch('widgets/content.images.tpl');
          $page['content'] = str_replace('%GALERI%', $rendered_tpl, $page['content']);
          $page['renders']['images'] = true;
        }

        if($this->str_contains($page['content'], '%KADRO_LISTE%')) {
          $this->core->view->append('page', $page);
          $rendered_tpl = $this->core->view->fetch('widgets/content.team.tpl');
          $page['content'] = str_replace('%KADRO_LISTE%', $rendered_tpl, $page['content']);
          $page['renders']['images'] = true;
        }
      }

      return $page;
    }

    public function lazy($str) {
			$match = ['<img src="', 'class="'];
			$replace = ['<img data-src="', 'class="lazy '];

			if(strpos($str, 'class="') == FALSE) {
				$str = str_replace("<img",'<img class=""',$str);
				$str = str_replace($match, $replace, $str);
			} else
				$str = str_replace($match, $replace, $str);

			return $str;
		}

    public function getRequest() {
      $request = explode('-',$this->core->requests[1]);
      $id = end($request);

      $slug = str_replace("-{$id}", '', implode('-', $request));

      return ['id' => $id, 'slug' => $slug, 'request' => $this->core->requests[1]];
    }

    public function notfound() {
  		header("HTTP/1.0 404 Not Found");

      $meta = [
        'title' => 'Aradığınız sayfa bulunamadı',
        'keywords' => '',
        'desc' => '',
        'slug' => '404',
        'name' => '404 Aradığınız Sayfa Bulunamadı'
      ];

      $this->core->view->append('meta', $meta);
  		$this->core->view->output('notfound.tpl', uniqid());
  		exit;
  	}

    public function statics() {
			/*
      $company = $this->get(['where' => ['id' => 2326], 'nodes' => true, 'multiple' => false, 'meta' => false, 'columns' => 'slug{},name{},content{}']);
      $company['nodes']['parameters'] = $this->mergeKeys($company['nodes']['parameters']);

			$whatsapp = $this->get(['where' => ['id' => 2417], 'nodes' => true, 'multiple' => false, 'meta' => false, 'columns' => 'name{}']);
			$whatsapp['nodes']['parameters'] = $this->mergeKeys($whatsapp['nodes']['parameters']);
			*/

      $this->getCookiePrograms();
			$parameters = $this->get(['where' => ['id' => 2595], 'nodes' => true, 'multiple' => false, 'meta' => false, 'columns' => 'name{}']);

      if($parameters) {
        $parameters['nodes']['parameters'] = $this->mergeKeys($parameters['nodes']['parameters']);
        $parameters = $parameters['nodes']['parameters'];
        $this->core->view->append('parameters', $parameters);
      }

      return;
			$css = file_get_contents(PATH . '/templates/'.$this->core->settings['templates']['theme'].'/assets/css/style.css');
			$css = str_replace(
        ['sourceMappingURL=', '{%$STATIC_PATH%}'],
        ["sourceMappingURL={$this->core->settings['view_config']['asset']}css/", $this->core->settings['domain']['static']],
        $css);

      $this->core->view->append('options', ['basvuru' => false]);
			$this->core->view->append('css', $css);
    }

    public function menuInit() {
      if(!empty($this->menu))
        return $this->menu;

			$this->menu = $this->core->QuerySingleValue("SELECT `value` FROM `system` WHERE `name` = 'MENU'");
      $this->menu = unserialize($this->menu);

      $this->menu = array_filter(array_map(function($e) {
        return $this->getAsMenu($e);
      }, $this->menu));

      $this->menu = (array_combine(array_column($this->menu, 'id'), $this->menu));
//       echo "
//       <!--
// ".print_r($this->menu,true)."
//       -->
//       ";
      return $this->menu;
		}

    private function getAsMenu($e) {
      if(!is_numeric($e['id']))
        return [];

      $_e = $this->get(
          [
            'where' => ['id' => $e['id']],
            'nodes' => true,
            'nodes_columns' => 'desc_long{tr},link_option,link_clickable,submenu,sl_order,single_page,page_icon,page_image1',
            'multiple' => false, 'meta' => false, 'columns' => 'link{},slug{},name{}'
          ]
        );

      if(!$_e) {
        return false;
      }

      if($e['sub'] == 1) {
        $_e['subs'] = $this->get(
          [
            'where' => ['parent' => $e['id']],
            'nodes' => true,
            'order' => (!empty($_e['nodes']['sl_order'])) ? [ ['id' => "{$_e['nodes']['sl_order']}", 'type' => 'FIELD'] ] : '',
            'nodes_columns' => 'desc_long{tr},link_option,link_clickable,submenu,page_icon,page_image1',
            'multiple' => true, 'meta' => false, 'columns' => 'link{},slug{},name{},desc{}'
          ]
        );
      }

      return $_e;
    }

		public function getContent($options = []) {
			switch ($options['type']) {
				case 'default':
					$content = $this->get(['where' => ['default' => 1], 'meta' => true, 'columns' => 'sl_template']);
				break;

				default:
				break;
			}

			return $content;
		}

		public function getBrand(int $id): array {
			if(!is_numeric($id))
				return [];

			$brand = $this->get(['where' => ['id' => $id], 'columns' => 'name{},slug{}']);

			if(!$brand)
				$brand = [];

			return $brand;
		}

		public function getImages($options = []) {
			if(isset($options['where']) AND !empty($options['where'])) {
				$options_defaults = [
					'where' => [],
					'limit' => 50
				];

				$options = array_merge($options_defaults, $options);

				if(!$options['where'])
					return [];

				$keys = array_keys($options['where']);

				for($i=0;$i<sizeof($keys);$i++) {
					$where[] = "`{$keys[$i]}` = ?";
					$data[] = $options['where'][$keys[$i]];
				}
				// 0 just image, 1 all images, 2 extended
				/*
				switch ($options['images_columns']) {
					case 1:
						// code...
					break;

					default:
						// code...
					break;
				}
				*/

				$where = implode(" AND ", $where);
				$images = $this->core->QueryArray("SELECT `images`, `values` FROM images WHERE {$where} LIMIT {$options['limit']}", $data);

				if(!empty($images) AND is_array($images)) {
					$images = array_map(function($e) {
						$e['images'] = unserialize($e['images']);
						$e['values'] = unserialize($e['values']);

						return $e;
					}, $images);
				}

				return $images;
			}
		}

		public function getFilters($options = []) {
			$where = [];
			$where_nodes = [];
			$data = [];

			$options_defaults = [
				'meta' => false,
				'nodes' => false,
				'images' => false,
				'where' => [
					'status' => 1
				],
				'where_nodes' => [],
				'debug' => false,
				'multiple' => false,
				'nodes_columns' => [],
				'images_columns' => [],
				'columns' => ''
			];

			$options = array_merge($options_defaults, $options);

			if(
				(isset($options['where']) AND !empty($options['where']))
				OR
				(isset($options['where_nodes']) AND !empty($options['where_nodes']))
			) {
				$keys = array_keys($options['where']);

				for($i=0;$i<sizeof($keys);$i++) {
					if(is_array($options['where'][$keys[$i]])) {
						$placeholders = str_repeat ('?, ',  count ($options['where'][$keys[$i]]) - 1) . '?';
						$where[] = "c.`{$keys[$i]}` IN ({$placeholders})";
						$data = array_merge($data,$options['where'][$keys[$i]]);
						//$data[] = $options['where'][$keys[$i]];
					} else {
						$where[] = "c.`{$keys[$i]}` = ?";
						$data[] = $options['where'][$keys[$i]];
					}
				}

				if(isset($options['where_nodes']) AND !empty($options['where_nodes'])) {
					$keys = array_keys($options['where_nodes']);

					for($i=0;$i<sizeof($keys);$i++) {
						if(is_array($options['where_nodes'][$keys[$i]])) {
							$placeholders = str_repeat ('?, ',  count ($options['where_nodes'][$keys[$i]]) - 1) . '?';
							$where_nodes[] = "(n.`value` IN ({$placeholders}) AND n.`key` = '{$keys[$i]}')";

							$data = array_merge($data,$options['where_nodes'][$keys[$i]]);
						} else {
							$where_nodes[] = "(n.`value` = ? AND n.`key` = '{$keys[$i]}')";

							$data[] = $options['where_nodes'][$keys[$i]];
						}
					}
				}

				$where[] = 'c.status = 1';
				$where[] = "
					(
						n.`key` = 'brand' OR
						n.`key` = 'packageType' OR
						n.`key` = 'product'
					)
				";

				$where = implode(' AND ', $where);
				$where_nodes = implode(' OR ', $where_nodes);

				if(!empty($where_nodes)) {
					$where_nodes = "
					AND c.id IN(SELECT DISTINCT
							cid FROM content_nodes n
						WHERE {$where_nodes}
						)
					";
				}

				$this->last_query = "SELECT
					n.`key`,
					n.`value`
				FROM
					contents c
					INNER JOIN content_nodes n
					ON n.cid = c.id
				WHERE
					{$where}
					{$where_nodes}
				GROUP BY n.`value`
				";

				$contents = $this->core->QueryArray($this->last_query, $data);
				/*
				$contents = array_map(function($e) {
					$e['nodes'] = $this->getNodes($e['id'], 'product,brand,packageType');

					return $e;
				}, $contents);
				*/
				return $contents;
			}

			return [];
		}

		public function get($options = []) {
			$where = [];
			$where_nodes = [];
			$data = [];
      $order = [];

			$options_defaults = [
				'meta' => false,
				'nodes' => false,
				'images' => false,
        'limit' => false,
				'where' => [
					'status' => 1
				],
				'where_nodes' => [],
				'debug' => false,
				'multiple' => false,
				'nodes_columns' => [],
				'images_columns' => [],
        'order' => [],
				'columns' => ''
			];

			$options = array_merge($options_defaults, $options);
			$this->debug = $options['debug'];

			if($this->debug)
				print_r($options);

			if(
				(isset($options['where']) AND !empty($options['where']))
				OR
				(isset($options['where_nodes']) AND !empty($options['where_nodes']))
			) {
				$keys = array_keys($options['where']);

				for($i=0;$i<sizeof($keys);$i++) {
          if(is_array($options['where'][$keys[$i]]) && isset($options['where'][$keys[$i]]['selector'])) {
            $where[] = "c.`{$keys[$i]}` {$options['where'][$keys[$i]]['selector']} {$options['where'][$keys[$i]]['value']} ";
            continue;
          }

          if(is_array($options['where'][$keys[$i]])) {
						$placeholders = str_repeat ('?, ',  count ($options['where'][$keys[$i]]) - 1) . '?';
						$where[] = "c.`{$keys[$i]}` IN ({$placeholders})";
						$data = array_merge($data,$options['where'][$keys[$i]]);
						//$data[] = $options['where'][$keys[$i]];
					} else {
						$where[] = "c.`{$keys[$i]}` = ?";
						$data[] = $options['where'][$keys[$i]];
					}
				}

				if(isset($options['where_nodes']) AND !empty($options['where_nodes'])) {
					$keys = array_keys($options['where_nodes']);

					for($i=0;$i<sizeof($keys);$i++) {
						if(is_array($options['where_nodes'][$keys[$i]])) {
							$placeholders = str_repeat ('?, ',  count ($options['where_nodes'][$keys[$i]]) - 1) . '?';
							$where_nodes[] = "(n.`value` IN ({$placeholders}) AND n.`key` = '{$keys[$i]}')";

							$data = array_merge($data,$options['where_nodes'][$keys[$i]]);
						} else {
							$where_nodes[] = "(n.`value` = ? AND n.`key` = '{$keys[$i]}')";

							$data[] = $options['where_nodes'][$keys[$i]];
						}
					}
				}

				$columns = '';

				if(!empty($options['columns'])) {
					$columns = explode(',', $options['columns']);

					$columns = array_map(function($e) {
						$col = str_replace('{}', "{{$this->prefix}}", $e);
						$colName = str_replace('{}', "", $e);

						return "`{$col}` AS `{$colName}`";
					}, $columns);

					$columns = ', ' . implode(', ', $columns);
				}

        if(!isset($options['where']['status']))
  				$where[] = 'status = 1';

        if(isset($options['order']) AND !empty($options['order'])) {
          $keys = array_keys($options['order']);

          for($j=0;$j<sizeof($options['order']);$j++) {
            if(!isset($keys[$j]))
              continue;

            if(isset($options['order'][$j]) AND $options['order'][$j]['type'] == 'FIELD')
              $order[] = "FIELD({$keys[$j]}, {$options['order'][$keys[$j]]['id']})";
            elseif(isset($options['order'][$j]))
              $order[] = "{$keys[$j]} {$options['order'][$j]['type']} {$options['order'][$j][$keys[$j]]}";
          }
        }

        if(isset($options['orderby']) AND !empty($options['orderby'])) {
          $keys = array_keys($options['orderby']);

          for($j=0;$j<sizeof($options['orderby']);$j++) {
            if(!isset($keys[$j]))
              continue;

            if(isset($options['orderby'][$keys[$j]]) AND $options['orderby'][$keys[$j]]['type'] == 'FIELD')
              $order[] = "FIELD({$keys[$j]}, {$options['orderby'][$keys[$j]]['value']})";
            elseif(isset($options['orderby'][$keys[$j]]))
              $order[] = "{$keys[$j]} {$options['orderby'][$j]['type']} {$options['orderby'][$j][$keys[$j]]}";
          }
        }

        $where = implode(' AND ', $where);

        if(!empty($order))
          $order = "ORDER BY " . implode(' ', $order);
        else
          $order = '';

        $where_nodes = implode(' OR ', $where_nodes);
        $limit = ($options['limit']) ? "LIMIT {$options['limit']}" : '';

				if(!empty($where_nodes)) {
					$this->last_query = "SELECT c.id {$columns} FROM contents c
						WHERE {$where} {$order}
						AND id IN (SELECT DISTINCT cid FROM content_nodes n WHERE {$where_nodes})
            {$limit}
					";
				} else {
					$this->last_query = "SELECT c.id {$columns} FROM contents c
						WHERE {$where} {$order}
            {$limit}
					";
				}
  			if($this->debug) {
          echo $this->last_query;
          print_r($data);
        }

        if(isset($options['multiple']) AND $options['multiple'] === true) {
					$contents = $this->core->QueryArray($this->last_query, $data);

					if($options['meta'] == true)
						$contents = array_map(function($e) use($options) {
							$e['meta'] = $this->getMeta($e['id']);
							$e['breadcrumb'] = $this->getBreadcrumb($e['id']);

							if(isset($options['lazy']) AND $options['lazy'] !== FALSE) {
								for($j=0;$j<sizeof($options['lazy']);$j++)
									if(isset($options['lazy'][$j])) {
										echo $e[$options['lazy'][$j]];
										$e[$options['lazy'][$j]] = $this->lazy($e[$options['lazy'][$j]]);
									}
							}
						}, $contents);

					if(!$contents)
						return [];

					if($options['images'] == true)
						$contents = array_map(function($e) use ($options) {
							$e['images'] = $this->getImages(['images_columns' => $options['images_columns'], 'where' => ['rid' => $e['id']]]);

							return $e;
						}, $contents);

					if($options['nodes'] == true)
						$contents = array_map(function($e) use ($options) {
							$e['nodes'] = $this->getNodes($e['id'], $options['nodes_columns']);

							return $e;
						}, $contents);

					return $contents;
				} else {
					$content = $this->core->QuerySingleRowArray($this->last_query, $data);

					if(!$content)
						return [];

						if($options['images'] == true)
						$content['images'] = $this->getImages(['images_columns' => $options['images_columns'], 'where' => ['rid' => $content['id']]]);

					if(!empty($content) AND $options['meta'] == true) {
						$content['meta'] = $this->getMeta($content['id']);
						$content['breadcrumb'] = $this->getBreadcrumb($content['id']);
					}

          if(isset($options['nodes']) AND $options['nodes'] == true)
						$content['nodes'] = $this->getNodes($content['id'], $options['nodes_columns']);

					return $content;
				}
			}

			return [];
		}

		public function getBreadcrumb($id = 0): Array {
			if(!$id)
				return [];

			$breadcrumb = [];

			for(;;) {
				$bread = $this->get(['nodes' => true, 'nodes_columns' => 'link_option,link_clickable', 'where' => ['id' => $id], 'columns' => 'name{},slug{},parent,link{}']);
				$breadcrumb[] = $bread;

				if($bread['parent'] == 0)
					break;
				else
					$id = $bread['parent'];
			}

			$breadcrumb = array_map(function($e) {
				$e['url'] = "{$this->core->settings['domain']['url']}/{$this->prefix}/{$e['slug']}-{$e['id']}";

				return $e;
			}, $breadcrumb);

			$breadcrumb = array_reverse($breadcrumb);

      return $breadcrumb;
		}

		public function getMeta($q) {
			$columns = [
				'title',
				'keywords',
				'desc',
				'slug',
				'name'
			];

			$columns = array_map(function($e) {
				return "`{$e}{{$this->prefix}}` AS `{$e}`";
			}, $columns);

			$columns = implode(',', $columns);

			if(is_numeric($q)) {
				$content = $this->core->QuerySingleRowArray("SELECT id, {$columns} FROM contents WHERE id = ?", [$q]);
				$content['title'] = !empty($content['title']) ? $content['title'] : $content['name'];
				$content['canonical'] = $this->core->settings['domain']['url'] ."/{$this->prefix}/". $content['slug'];

				return $content;
			}

			if(is_array($q))
				$contents = array_map(function($e) {
					$content = $this->core->QuerySingleRowArray("SELECT id, {$columns} FROM contents WHERE id = ?", [$e['id']]);

					$content['title'] = !empty($content['title']) ? $content['title'] : $content['name'];
					$content['canonical'] = $this->core->settings['domain']['url'] .'/'. $content['slug'];
					return $content;
				}, $q);

			return $contents;
		}

		public function mergeKeys($params) {
			$values = [];

			if(!is_array($params) OR empty($params))
				return;

			for($i=0;$i<sizeof($params['name']);$i++) {
				$values[$params['name'][$i]] = $params['text'][$i];
			}

			return $values;
		}

		public function getModule($mid) {
			if(!$mid)
				return;

			return $this->core->QuerySingleRowArray("SELECT * FROM content_modules WHERE id = ?", [$mid]);
		}

		public function getNodes($q, $columns = []) {
      if(is_numeric($q)) {
				$data = [$q];

				if(!empty($columns)) {
					$data = [];
					$data[] = $q;
					$columns = explode(',', $columns);
					$data = array_merge($data, $columns);
					$placeholders = str_repeat ('?, ',  count ($columns) - 1) . '?';

					$columns = "AND `key` IN ({$placeholders})";
				} else {
					$columns = '';
				}

				$_nodes = $this->core->QueryArray("SELECT `key`, `value` FROM content_nodes WHERE cid = ? {$columns}", $data);
				$nodes = [];

				for($i=0;$i<sizeof($_nodes);$i++) {
					if(
						strpos($_nodes[$i]['key'], "{{$this->prefix}}") > 0
					) {
						$key = str_replace("{{$this->prefix}}", "", $_nodes[$i]['key']);
						$nodes[$key] = ($this->core->isSerial($_nodes[$i]['value'])) ? unserialize($_nodes[$i]['value']) : $_nodes[$i]['value'];
						$nodes[$key] = ($nodes[$key]);
					} elseif(
						!strpos($_nodes[$i]['key'], "{")
					) {
						$nodes[$_nodes[$i]['key']] = $_nodes[$i]['value'];
						$nodes[$_nodes[$i]['key']] = ($this->core->isSerial($_nodes[$i]['value'])) ? unserialize($_nodes[$i]['value']) : $_nodes[$i]['value'];
						$nodes[$_nodes[$i]['key']] = ($nodes[$_nodes[$i]['key']]);
					}
				}

        return $nodes;
			}

			if(is_array($q))
				$contents = array_map(function($e){
					$e['nodes'] = $this->getNodes($e['id'], $columns);

					return $e;
				}, $q);
		}
  }
}
