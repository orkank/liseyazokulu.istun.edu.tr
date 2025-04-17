
<?php
/**
*	Original File Name	:	class.sl.php
*	Version							:	3.0
*	Created							:	Wed, 05-06-2019
*	Modified						:	Wed, 05-06-2019
*	Copyright						:	Copyright (C) 2019 Orkan KÖYLÜ
*	Author							:	Orkan KÖYLÜ
*/
if (!defined('SL')) header("Location: /404");

class SL {
	/*
	* Debug mode
	* 0 = disabled, 1 = enabled
	*/
	var $debug		= 1;
	var $debug_ips = array();

	/*
	* Engine status
	* 0 = production mode
	* 1 = Read-only is on, read-only mode limited functionally
	*/
	var $mode		= 'dev';

	/*
	* General token
	*/
	var $token	= '';

	/*
	* Loop limit
	* @var int
	*/
	var $loop 		= 100;

	/*
	* Illegal action limit
	* @var int
	*/
	var $illegal	= 3;

	/*
	* Client side slugs
	* @var string
	*/
	var $rules;

	/*
	* Portal name
	* @var string
	*/
	public $portal_title = 'Silüet 3.0 Pro';
	public $engine_url = 'https://www.siluet.net';

	/*
	* User
	* @var array
	*/
	public $user = array();
	public $user_groups = array();

	/*
	* SL modules
	* @var array
	*/
	var $modules = array();

	/*
	* _POST _GET variables
	* @var array
	*/
	var $post = array(),
			$get = array();

	public
				$alert_variables,
				$langs,
				$lang,
				$db,
				$smarty,
				$settings,
				$scripts,
				$content,
				$sl_modules,
				$sl_module,
				$config,
				$columns,
				$menu,
				$page,
				$nocomment = false;

	const ALERT_WARNING			= "warning";
	const ALERT_INFO				= "info";
	const ALERT_ERROR				= "danger";
	const ALERT_DANGER			= "danger";
	const ALERT_SUCCESS			= "success";

	private
	$default_headers = array(
		["X-Frame-Options", "SAMEORIGIN"],
		["X-Version", "SLSS 5.00"],
		["X-Powered-By", "SL Pro"],
		["X-Version", "1.0"],
		["Access-Control-Allow-Origin", "/"],
		["Cache-Control", "no-store, no-cache, must-revalidate"],
    /*
		["Access-Control-Allow-Headers", "X-Requested-With, Content-Type, Accept, Origin, Authorization"],
		["Access-Control-Allow-Methods", "GET, POST, PUT, DELETE, PATCH, OPTIONS"]
    */
	);

	public function writeHeader($list): void {
		if(isset($list['key'])) {
			header("{$list['key']}: {$list['value']}");

			return;
		}

		if(is_array($list)) {
			$keys = array_keys($list);
			for($i=0;$i<sizeof($list);$i++) {
				header("{$list[$i][0]}: {$list[$i][1]}");
			}
		}
	}

	public function setDefaultTemplate() {
		$this->smarty
			->setTemplateDir(
				$this->settings['theme_path']
			)
			->setCompileDir($this->settings['theme_c'])
			->setCacheDir($this->settings['cache']);
	}

	public function __construct(&$settings) {
		$this->TimerStart();

    $this->settings = $settings;
		// $this->session();

    $this->writeHeader($this->default_headers);
		$this->token();

		$this->smarty = new Smarty;

		$this->smarty->auto_literal = false;
		$this->smarty->left_delimiter = "<%";
		$this->smarty->right_delimiter = "%>";

		require(PB . '/portal/includes/sl.smarty.plugins.php');

		//$this->setDefaultTemplate();

	 if($settings['MODE'] == 'pro') {
		 //$this->smarty->loadFilter('output', 'trimwhitespace');
		 //define("HTML_MINIFY_URL", $settings['url']);
		 //$this->smarty->registerFilter("output", "minify_html");
	 }

		//$policy = new Smarty_Security($this->smarty);
		//$this->smarty->enableSecurity($policy);

		$this->smarty->force_compile  = true;
		$this->smarty->debugging      = false;
		$this->smarty->caching        = true;
		$this->smarty->setCaching(Smarty::CACHING_LIFETIME_CURRENT);
		// set the cache_lifetime for index.tpl to 5 minutes
		$this->smarty->setCacheLifetime(0);
		$this->smarty->setCompileCheck(true);

		$this->db = new MySQL(true,
			$settings['database']['db'],
			$settings['database']['host'],
			$settings['database']['user'],
			$settings['database']['password'],
			$settings['database']['port'],
		'utf8', false);

		$this->post = $_POST;
		$this->get = $_GET;

		/*
		if(!empty($_POST))
			$this->post = $this->array_map_recursive('addslashes', filter_input_array(INPUT_POST));

		if(!empty($_GET))
			$this->get = $this->array_map_recursive('addslashes', filter_input_array(INPUT_GET));
		*/
		/*
		$keys = array_keys($this->post);
		for($i=0;$i<sizeof($this->post);$i++) {
			$this->post[$keys[$i]] = mysqli_real_escape_string($this->db->mysql_link, $this->post[$keys[$i]]);
		}

		$keys = array_keys($this->get);
		for($i=0;$i<sizeof($this->get);$i++) {
			$this->get[$keys[$i]] = mysqli_real_escape_string($this->db->mysql_link, $this->get[$keys[$i]]);
		}
		*/

		$this->rules();
		$this->user_groups = $this->db->QueryArray("SELECT * FROM `user_groups` ORDER BY `title` ASC", MYSQLI_ASSOC, 'id');

		if($this->rules[0] == 'en') {
			$this->notfound();
			exit;
		}

		$this->settings	= $settings;

		$this->alert(false, 'read');
		$this->cookie_timeout = time() + $this->settings['cookie']['lifetime'];
		$this->languages('variables');

		$this->smarty->assign('langs',$this->languages('langs'));

		$this->smarty->assign('settings',$settings);
		$this->smarty->assign('prefix',$this->languages('prefix'));

		if(!$this->db->IsConnected()) {
		  $this->smarty->assign('error','MySQL Bağlantısı Kurulamıyor');
		  $this->smarty->display('mysql.error.html');

		  exit;
		}

		if(isset($settings['mode']))
			$this->mode($settings['mode']);

		$this->smarty->assign('devicetype', $this->devicetype());
		$this->cookie = @(mysqli_real_escape_string($this->db->mysql_link, $_COOKIE[$this->settings['cookie']['name']]));
	}

	public function nocomment($set = false) {
		if($set)
			$this->nocomment = true;
		else
			$this->nocomment = false;

		return
		@(
			$this->post['action'] == 'rpc' OR
			$this->get['action'] == 'rpc' OR
			$this->rules_variables[0] == 'rpc' OR
			strpos($_SERVER['REQUEST_URI'], 'rpc') OR
			!$this->nocomment
		)
		?false:true;
	}

	/*
	* Debug mode function
	* Returns boolean, true/false
	* Accepts array, single IP
	*/
	public function debug($val = false) {
		if (filter_var($val, FILTER_VALIDATE_IP)) {
			$this->debug_ips[] = $val;
		}

		if(is_array($val)) {
			$this->debug_ips = $val;
		}

		$this->debug = (in_array($this->GetIP(),$this->debug_ips))?1:0;

		return ($this->debug)? true:false;
	}

	/*
	* Engine status return value is boolean
	*/
	public function mode($int = 0) {
		if(is_numeric($int)) {
			$this->mode = $int;
		}

		return ($this->mode == 0)? true:false;
	}

	public function nocache() {
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	}

	public function PHPSESSID() {
		return ($_COOKIE[$this->settings['cookie']['session']] == session_id())?true:false;
	}

	public function prepare_scripts() {
		switch ($this->rules[2]) {
			case 'frontend':
				$config = file_get_contents(PB .DS. 'templates' .DS. 'default' .DS. 'assets' .DS. 'js' .DS. 'general.js.json');

				if($config) {
					$config = json_decode($config, true);
				}

				$contents = "
				/*
				* Created by Silüet
				* Date created: ".date('Y-m-d H:i:s')."
				*
				*/\n\n";
				for($i=0;$i<sizeof($config['files']);$i++) {
					$config['files'][$i] = str_replace('../', '', $config['files'][$i]);

					if(
						is_file(PB .DS. 'templates' .DS. 'default' .DS. 'assets' .DS. $config['files'][$i]) OR
						filter_var($config['files'][$i], FILTER_VALIDATE_URL)
					) {
						$contents .=
						filter_var($config['files'][$i], FILTER_VALIDATE_URL)?
						file_get_contents($config['files'][$i]) . "\n":
						file_get_contents(PB .DS. 'templates' .DS. 'default' .DS. 'assets' .DS. $config['files'][$i]) . "\n";
					}
				}

				return $contents;
			break;

			default:
				// code...
			break;
		}
		//$config = $this->dir(PB .DS. 'templates' .DS. 'default' .DS. 'assets' .DS. 'js' .DS. 'general.js.json');
	}

	function getRequestHeaders() {
	    $headers = array();
	    foreach($_SERVER as $key => $value) {
	        if (substr($key, 0, 5) <> 'HTTP_') {
	            continue;
	        }
	        $header = str_replace(' ', '-', (str_replace('_', ' ', (substr($key, 5)))));
	        $headers[$header] = $value;
	    }
	    return $headers;
	}

	public function checkToken($str) {
		if (!empty($this->token())) {
				if (!hash_equals($sl->token(), $sl->getRequestHeaders()['X-CSRF-TOKEN'])) {
					header("Location: /");
					exit;
				}
		} else {
			header("Location: /");
			exit;
		}
	}

	public function token($str = '') {
		if(!empty($str)) {
			return hash_hmac('sha256', 'SL', $this->token);
		} else {
			if (empty($_SESSION['token'])) {
			    $_SESSION['token'] = bin2hex(random_bytes(32));
			}

			$this->token = $_SESSION['token'];

			return $this->token;
		}
	}

	public function languages($com = false, $variable = '') {
		if(empty($this->lang_default))
			$this->lang_default = $this->db->QuerySingleRowArray("SELECT `id`,`name`,`prefix`,`code` FROM `languages` WHERE `default`='1'", MYSQLI_ASSOC);

		if(empty($this->langs))
			$this->langs = $this->db->QueryArray("SELECT `id`,`name`,`prefix`,`code` FROM `languages`", MYSQLI_ASSOC, 'prefix');

		if((isset($this->langs[$this->rules[0]]) OR @ $this->user['lang'] > 0) AND empty($this->lang)) {
			$this->lang = (@$this->user['lang'] > 0)?
				$this->db->QuerySingleValue("SELECT `prefix` FROM `languages` WHERE `id`='".$this->user['lang']."'")
			:$this->rules[0];
		} else {
			if(empty($this->lang))
				$this->lang = $this->db->QuerySingleValue("SELECT `prefix` FROM `languages` WHERE `default`='1'");
		}

		if($this->lang) {
			switch ($com) {
				case 'prefix':
					return $this->langs[$this->lang]['prefix'];
				break;
				case 'langs':
					return array_values($this->langs);
				break;
				case 'variables':
					if(!isset($this->lang_variables) AND isset($this->langs[$this->lang]['variables'])) {
						$this->lang_variables = unserialize($this->langs[$this->lang]['variables']);
					} else {
						$this->lang_variables = '';
					}

					return $this->lang_variables;
				break;

				default:
					if(isset($this->lang_variables[$com])) {
						return $this->lang_variables[$com];
					} else {
						return $com;
					}
				break;
			}
		} else {
			return false;
		}

		return $this->langs;
	}

	public function JSONHeaders() {
		header("Content-Type: application/json");
		header("Expires: 0");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
	}

	public function config() {
    if( @ !$this->config[md5($this->sl_module['config'])])
      $this->config[md5($this->sl_module['config'])] = json_decode(file_get_contents(PB . $this->settings['portal_url'] . 'config' .DS. $this->sl_module['config']), true);

    return $this->config[md5($this->sl_module['config'])];
  }

	public function columns($table = '') {
    if(!$table)
      return;

		$this->columns = array_column(
      $this->db->QueryArray("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '".$table."'", MYSQLI_ASSOC, 'COLUMN_NAME'),
       'COLUMN_NAME', 'COLUMN_NAME');

    $this->columns = preg_filter('/^/', '`', $this->columns);
    $this->columns = preg_filter('/$/', '`', $this->columns);

    return $this->columns;
	}

	public function images($id = '', $group = 1, $slug = 'images{tr}') {
		$sql = "SELECT * FROM `images`
			WHERE
			`rid`='".(!empty($id)?$id:$this->content['id'])."' AND
			`group`='".(!empty($group)?$group:$this->content['sl_module']['group'])."' AND
			`slug` = '{$slug}'
			ORDER BY `sort` ASC
			";

		$images = $this->db->QueryArray($sql,MYSQLI_ASSOC);

		for($i=0;$i<sizeof($images);$i++) {
			$images[$i]['images'] = unserialize($images[$i]['images']);
			$images[$i]['values'] = unserialize($images[$i]['values']);
		}

		$images = $this->changekeys($images,
			array(
				'title{'.$this->languages('prefix').'}' => 'title',
				'name{'.$this->languages('prefix').'}' => 'name',
				'email{'.$this->languages('prefix').'}' => 'email',
			)
		);

		if(!$id)
			$this->content['images'] = $images;

		return $images;
	}

	public function changekeys($arr, $set = false) {
		if(!$set) {
			$_arr = array();
			$keys = array_keys($arr);

			for($i=0;$i<sizeof($arr);$i++) {
				if(strpos($keys[$i], '{prefix}') !== FALSE) {
					$key = str_replace('{prefix}', "{{$this->languages('prefix')}}", $keys[$i]);
					$_arr[$key] = $arr[$keys[$i]];
				} else {
					$_arr[$keys[$i]] = $arr[$keys[$i]];
				}
			}

			return $_arr;
		} else {
			if (is_array($arr) && is_array($set)) {
	  		$newArr = array();
	  		foreach ($arr as $k => $v) {
	  		    $key = array_key_exists( $k, $set) ? $set[$k] : $k;
	  		    $newArr[$key] = is_array($v) ? $this->changekeys($v, $set) : $v;
	  		}

	  		return $newArr;
  		}
		}

  	return $arr;
  }

	public function slug($content = false, $id = false) {
		$slug = array();

		if($content) {
			$slug[] = (!empty($content['slug'])) ? $content['slug'] : $content["slug{{$this->languages('prefix')}}"];
			if($content['parent'] != 0) {
				$link = $this->db->QuerySingleValue("SELECT `slug{{$this->languages('prefix')}}` FROM `contents` WHERE `id` = {$content['parent']} AND `link_access` = 1");

				if($link)
					$slug[] = $link;
			}
		}

		$slug = array_reverse($slug);
		return implode('/', $slug);
	}

	public function contents(
		$where = array(),
		$multiple = false,
		$limit = 1,
		$offset = 0,
		$recursive = false,
		$columns = array(),
		$order = array()
	) {

		$slug = end($this->rules);

		if(empty($slug)) {
			$slug = prev($this->rules);
		}

		$master = $this->rules[1] == $slug ? false : $this->rules[1];

		if($master == $this->languages('prefix') OR empty($master)) {
			$id = $this->db->QuerySingleValue("SELECT `id` FROM `contents` WHERE `status` = 1 AND `slug{{$this->languages('prefix')}}` = '{$slug}'");

			$sl_order = $this->db->QuerySingleValue("SELECT `value` FROM `content_nodes` WHERE `cid` = {$id} AND `key`='sl_order'");
		} else {
			$master = $this->db->QuerySingleRowArray("SELECT `id` FROM `contents` WHERE `status` = 1 AND `slug{{$this->languages('prefix')}}` = '{$master}'");
			$master_id = $master['id'];

			$i = 1;
			$master_prev = $master_id;

			for(;;) {
				$i++;

				if(!isset($this->rules[$i]) OR (sizeof($this->rules)-1) == $i) {
					break;
				}

				$_slug = $this->rules[$i];

				//$master_prev = prev($this->rules);
				$master_prev = $this->db->QuerySingleValue("SELECT `id` FROM `contents` WHERE `status` = 1 AND `slug{{$this->languages('prefix')}}` = '{$_slug}' AND `parent` = {$master_prev}");
			}

			$master_prev = empty($master_prev) ? $master_id : $master_prev;

			$sl_order = $this->db->QuerySingleValue("SELECT `value` FROM `content_nodes` WHERE `cid` = {$master['id']} AND `key`='sl_order'");
		}

		if(empty($order) AND !empty($sl_order))
			$order = $sl_order;

		switch ($where) {
			case 'slug':
				$queries[] = "(`c`.`slug{{$this->languages('prefix')}}` = '{$slug}')";

				if(is_numeric($master_prev) AND $master_prev > 0)
					$queries[] = "`c`.`parent` = {$master_prev}";
			break;

			default:
				$where = $this->changekeys($where);

				$queries = array_map(function($value, $key) {
					if(is_array($value)) {
						return "`{$key}`{$value[1]}'{$value[0]}'";
					} else {
						return "`{$key}`='{$value}'";
					}
				}, array_values($where), array_keys($where));
			break;
		}

		$queries[] = "(`c`.`status`='1' OR `c`.`status`='3')";

		$query = implode(" AND ", $queries);

		$md5 = md5($query);

		$this->columns($this->module['table']);

		if(isset($this->contents[$md5])) {
			$this->content = $this->contents[$md5];

			return $this->contents[$md5];
		}

		if(!isset($this->sl_modules))
			$this->sl_modules = $this->db->QueryArray("SELECT * FROM `content_modules` WHERE `type`='2'",MYSQLI_ASSOC,'id');

		$_query = "SELECT `c`.`sl_module` FROM `contents` AS `c` WHERE {$query}";
		$sl_module = $this->db->QuerySingleValue($_query);

		if(isset($this->sl_modules[$sl_module])) {
			$this->sl_module = $this->sl_modules[$sl_module];
			$config = $this->config();
		}

		if($sl_module) {
			$columns = array();

			for($i=0;$i<sizeof($config['tabs']);$i++) {
				if(!isset($config['tabs'][$i]['inputs']))
					continue;

				for($s=0;$s<sizeof($config['tabs'][$i]['inputs']);$s++) {
					if(!isset($config['tabs'][$i]['inputs'][$s]['multilanguage']))
						$config['tabs'][$i]['inputs'][$s]['multilanguage'] = '0';
					if(!isset($config['tabs'][$i]['inputs'][$s]['serialize']))
						$config['tabs'][$i]['inputs'][$s]['serialize'] = '0';
					if(!isset($config['tabs'][$i]['inputs'][$s]['type']))
						$config['tabs'][$i]['inputs'][$s]['type'] = '';

					if(
						isset($this->columns["{$config['tabs'][$i]['inputs'][$s]['slug']}{{$this->languages('prefix')}}"]) OR
						isset($this->columns[$config['tabs'][$i]['inputs'][$s]['slug']])
						)
						$columns['columns'][] =
						($config['tabs'][$i]['inputs'][$s]['multilanguage'] == 1)?
						"`c`.`{$config['tabs'][$i]['inputs'][$s]['slug']}{{$this->languages('prefix')}}` AS `{$config['tabs'][$i]['inputs'][$s]['slug']}`"
						:"`c`.`{$config['tabs'][$i]['inputs'][$s]['slug']}`";
					else
						$columns['nodes'][] = array(
							'name' => ($config['tabs'][$i]['inputs'][$s]['multilanguage'] == 1)?
							"{$config['tabs'][$i]['inputs'][$s]['slug']}{{$this->languages('prefix')}}"
							:"{$config['tabs'][$i]['inputs'][$s]['slug']}",
							'slug' => $config['tabs'][$i]['inputs'][$s]['slug'],
							'serialize' => $config['tabs'][$i]['inputs'][$s]['serialize'],
							'type' => $config['tabs'][$i]['inputs'][$s]['type']
						);
				}
			}

			/*
			for($i=0;$i<sizeof($this->config['table']['columns']);$i++)
				$columns[] = "`{$this->config['table']['columns'][$i]['name']}".($this->config['table']['columns'][$i]['multilanguage'] == 1?"{{$this->languages('prefix')}}":'')."` AS `{$this->config['table']['columns'][$i]['name']}`";
				*/
			if($multiple) {
				$limit = ($limit > 0) ? "LIMIT {$limit}" : '';
				$offset = ($limit > 0) ? "OFFSET {$offset}" : '';

				/*
				echo '<!--';
				print_r($order);
				echo '-->';
				*/
				if(
					is_array($order) AND
					$order['type'] == 'node'
				) {
					/*
					$order['item'] = explode(',', $order['items']);
					$order['item'] = array_map(function($e){
						return "`n`.`{$e}`";
					}, $order['item']);
					$order['items'] = implode(',', $order['items']);
					*/
					//$order = !empty($order['item'])?"ORDER BY `n`.`{$order['item']}` {$order['order']}" :"ORDER BY `id` DESC";
					$sql = "
					SELECT
						*
					FROM (
						SELECT
							`c`.`id`,
							`c`.`modified`,
							`c`.`published`,
							`c`.`breadcrumb`,
							`c`.`sl_module`,
							`c`.`sl_template`,
							`n`.`value` AS `{$order['item']}`,
							".implode(",\n",$columns['columns'])."
						FROM
							`contents` AS `c`
							INNER JOIN `content_nodes` AS `n`
	        	ON
							c.id = n.cid
						WHERE
							{$query}
							AND `n`.`key` = '{$order['item']}'
						) AS `data`
					ORDER BY STR_TO_DATE(`{$order['item']}`,'%d.%m.%Y') {$order['order']}
					{$limit} {$offset}
						";

					$this->contents[$md5] = $this->db->QueryArray($sql,MYSQLI_ASSOC);
				} else {
					if(is_array($order)) {
						switch($order['type']) {
							case 'column':
							$order = !empty($order)?"ORDER BY {$order['column']} ASC" :"ORDER BY `id` DESC";
							break;
							default:
							$order = "ORDER BY STR_TO_DATE(`{$order['item']}`,'%d.%m.%Y') {$order['order']}";
						}
					} else {
						$order = !empty($order)?"ORDER BY FIELD(`id`, {$order}) ASC" :"ORDER BY `id` DESC";
					}

					$query = "SELECT
						`c`.`id`,
						`c`.`modified`,
						`c`.`published`,
						`c`.`breadcrumb`,
						`c`.`sl_module`,
						`c`.`sl_template`,
						".implode(",\n",$columns['columns'])."
						FROM `contents` AS `c` WHERE {$query} {$order} {$limit} {$offset}";

					$this->contents[$md5] = $this->db->QueryArray($query,MYSQLI_ASSOC);
				}

					for($s=0;$s<sizeof($this->contents[$md5]);$s++) {
						for($i=0;$i<sizeof($columns['nodes']);$i++) {
							$this->contents[$md5][$s][$columns['nodes'][$i]['slug']] =
								$this->db->QuerySingleValue("SELECT `value` FROM `content_nodes` WHERE
									`cid`= '{$this->contents[$md5][$s]['id']}' AND
									`key`='{$columns['nodes'][$i]['name']}' AND (`onload` = 1 OR `onload` = 0)
								",MYSQLI_ASSOC);

							if($columns['nodes'][$i]['serialize'] == 1)
								$this->contents[$md5][$s][$columns['nodes'][$i]['slug']] = unserialize($this->contents[$md5][$s][$columns['nodes'][$i]['slug']]);

							if($columns['nodes'][$i]['type'] == 'datepicker') {
								$this->contents[$md5][$s][$columns['nodes'][$i]['slug']] = $this->date($this->contents[$md5][$s][$columns['nodes'][$i]['slug']]);
							}
						}

						$this->contents[$md5][$s]['sl_module'] = $this->sl_module;
						$this->contents[$md5][$s]['meta'] = $this->meta($this->contents[$md5][$s]);

						if($recursive)
							$this->contents[$md5][$s]['subs'] = $this->contents(
								array('parent' => $this->contents[$md5][$s]['id']), true, false, false, false, false, $this->contents[$md5][$s]['sl_order']);
					}
			} else {
				$this->contents[$md5] = $this->db->QuerySingleRowArray("SELECT
					`c`.`id`,
					`c`.`modified`,
					`c`.`published`,
					`c`.`breadcrumb`,
					`c`.`sl_module`,
					`c`.`sl_template`,
					".implode(",\n",$columns['columns'])."
					FROM `contents` AS `c` WHERE {$query}",MYSQLI_ASSOC);

					for($i=0;$i<sizeof($columns['nodes']);$i++) {
						$this->contents[$md5][$columns['nodes'][$i]['slug']] =
							$this->db->QuerySingleValue("SELECT `value` FROM `content_nodes` WHERE
								`cid`= '{$this->contents[$md5]['id']}' AND
								`key`='{$columns['nodes'][$i]['name']}'
							",MYSQLI_ASSOC);

						if($columns['nodes'][$i]['serialize'] == 1)
							$this->contents[$md5][$columns['nodes'][$i]['slug']] = unserialize($this->contents[$md5][$columns['nodes'][$i]['slug']]);

						if($columns['nodes'][$i]['type'] == 'datepicker')
							$this->contents[$md5][$columns['nodes'][$i]['slug']] = $this->date($this->contents[$md5][$columns['nodes'][$i]['slug']]);
					}

					$this->contents[$md5]['sl_module'] = $this->sl_module;
					$this->contents[$md5]['meta'] = $this->meta($this->contents[$md5]);
					$this->contents[$md5]['master_id'] = $master_id;
			}

			$this->content = $this->contents[$md5];
			return $this->content;
		} else {
			return false;
		}
	}

	function datelocalization($str = '') {
		$months = array(
			"January", "February", "March", "April", "May", "June",
			"July", "August", "September", "October", "November", "December"
		);

		$days = array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");

		$months_local = array(
			"Ocak", "Şubat", "Mart", "Nisan", "Mayıs", "Haziran",
			"Temmuz", "Ağustos", "Eylül", "Ekim", "Kasım", "Aralık"
		);

		$days_local = array("Pazartesi", "Salı", "Çarşamba", "Perşembe", "Cuma", "Cumartesi", "Pazar");

		$str = str_replace($months, $months_local, $str);
		$str = str_replace($days, $days_local, $str);

		return $str;
	}

	public function date($date = '') {
		if(empty($date))
			$date = date('Y-m-d H:i:s');

		$_ = $date;
		$date = array();
		/*
		$date = explode(
			(
				strpos('.',$date)		=== FALSE?'.':
				(
					strpos('-',$date)	=== FALSE?'-':
					strpos('/',$date)	=== FALSE?'/':
					' '
				)
			),
			$date
		);
		*/

		$date['stamp'] = strtotime($_);
		$date['unchanged'] = $_;
		$date['week'] = date('W', $date['stamp']);
		$date['ymt'] = date('Y-m-t', $date['stamp']);
		$date['fulldate'] = date('Y-m-d');

		$date['day'] =
		array(
			'int' => date('d', $date['stamp']),
			'str_short' => date('D', $date['stamp']),
			'str_long' => $this->datelocalization(date('l', $date['stamp']))
		);

		$date['month'] =
		array(
			'int' => date('m', $date['stamp']),
			'str_short' => date('M', $date['stamp']),
			'str_long' => $this->datelocalization(date('F', $date['stamp'])),
		);
		$date['year'] = date('Y', $date['stamp']);
		$date['year_short'] = date('y', $date['stamp']);

		return $date;
	}

	function is_serialized( $data, $strict = true ) {
	    // if it isn't a string, it isn't serialized.
	    if ( ! is_string( $data ) ) {
	        return false;
	    }
	    $data = trim( $data );
	    if ( 'N;' == $data ) {
	        return true;
	    }
	    if ( strlen( $data ) < 4 ) {
	        return false;
	    }
	    if ( ':' !== $data[1] ) {
	        return false;
	    }
	    if ( $strict ) {
	        $lastc = substr( $data, -1 );
	        if ( ';' !== $lastc && '}' !== $lastc ) {
	            return false;
	        }
	    } else {
	        $semicolon = strpos( $data, ';' );
	        $brace     = strpos( $data, '}' );
	        // Either ; or } must exist.
	        if ( false === $semicolon && false === $brace ) {
	            return false;
	        }
	        // But neither must be in the first X characters.
	        if ( false !== $semicolon && $semicolon < 3 ) {
	            return false;
	        }
	        if ( false !== $brace && $brace < 4 ) {
	            return false;
	        }
	    }
	    $token = $data[0];
	    switch ( $token ) {
	        case 's':
	            if ( $strict ) {
	                if ( '"' !== substr( $data, -2, 1 ) ) {
	                    return false;
	                }
	            } elseif ( false === strpos( $data, '"' ) ) {
	                return false;
	            }
	            // or else fall through
	        case 'a':
	        case 'O':
	            return (bool) preg_match( "/^{$token}:[0-9]+:/s", $data );
	        case 'b':
	        case 'i':
	        case 'd':
	            $end = $strict ? '$' : '';
	            return (bool) preg_match( "/^{$token}:[0-9.E+-]+;$end/", $data );
	    }
	    return false;
	}

	public function nodes($id) {
		$nodes = $this->db->QueryArray("SELECT
			`id`,
			`key`,
			`value`
			FROM `content_nodes`
			WHERE `cid`='{$id}' AND `onload`='1'
			", MYSQLI_ASSOC, 'key');

		if(is_array($nodes) AND count($nodes) > 0) {
			$nkeys = array_keys($nodes);
			for($n=0;$n<sizeof($nodes);$n++)
				$nodes[$nkeys[$n]]['value'] =
					(
						$this->is_serialized($nodes[$nkeys[$n]]['value'])?
					unserialize($nodes[$nkeys[$n]]['value'])
					:$nodes[$nkeys[$n]]['value']
				);

				$nodes = $this->changekeys($nodes,
					array(
						'mname{'.$this->languages('prefix').'}' => 'mname',
					)
				);
		}

		return $nodes;

		for($i=0;$i<sizeof($items);$i++) {
		}
	}

  public function get_menu_static() {
    return json_decode(file_get_contents(PB . DS . 'menu.json'), true);
  }

	public function getmenu($_X = '') {
		if(!$_X) {
			if(!empty($this->menu))
				return $this->menu;

      /*
			$cache = $this->db->QuerySingleRowArray("SELECT * FROM `cache` WHERE `name`='MENU'");
			if($cache['expiry'] > time()) {
				echo '<!-- return from cache -->';
				return $this->decode(unserialize($cache['content']));
			}
      */

			$_X = $this->db->QuerySingleValue("SELECT `value` FROM `system` WHERE `name`='MENU'");
			$_X = unserialize($_X);
		} else {
			if(strpos(',', $_X)) {
				$_X = explode(',', $_X);
			} elseif(!is_array($_X)) {
				$_X = array($_X);
				//$_X[] = $_X;
			}
		}
		/*
		$this->menu = array_map(function($item) {
			return "`id`='{$item}'";
		}, $this->menu);
		*/
    if($_X) {
			for($i=0;$i<sizeof($_X);$i++) {
				$this->menu[$i] = $this->db->QuerySingleRowArray("SELECT
					`id`,
					IF(`name{{$this->languages('prefix')}}` != '', `name{{$this->languages('prefix')}}`, `name{{$this->lang_default['prefix']}}`) AS `name`,
					IF(`default` = 0, IF(`slug{{$this->languages('prefix')}}` != '', `slug{{$this->languages('prefix')}}`, `slug{{$this->lang_default['prefix']}}`), '') AS `slug`,
					`breadcrumb`,
					`orders`,
					`sl_module`,
					`sl_template`,
					IF(`link{{$this->languages('prefix')}}` != '', `link{{$this->languages('prefix')}}`, `link{{$this->lang_default['prefix']}}`) AS `link`,
					IF(`desc{{$this->languages('prefix')}}` != '', `desc{{$this->languages('prefix')}}`, `desc{{$this->lang_default['prefix']}}`) AS `desc`
					FROM `contents`
					WHERE `id`='{$_X[$i]['id']}' AND `status`='1'
					", MYSQLI_ASSOC);

				$this->menu[$i]['menu'] = $_X[$i];

				$this->menu[$i]['nodes'] = $this->nodes($_X[$i]['id']);

				if(empty($this->menu[$i]))
					unset($this->menu[$i]);

				for($s=0;$s<sizeof($this->menu);$s++) {
					$order = !empty($this->menu[$s]['nodes']['sl_order']['value'])?"ORDER BY FIELD(`id`, {$this->menu[$s]['nodes']['sl_order']['value']})" :"";

					$_subs = $this->db->QueryArray("SELECT
						`id`,
						IF(`name{{$this->languages('prefix')}}` != '', `name{{$this->languages('prefix')}}`, `name{{$this->lang_default['prefix']}}`) AS `name`,
						IF(`default` = 0, IF(`slug{{$this->languages('prefix')}}` != '', `slug{{$this->languages('prefix')}}`, `slug{{$this->lang_default['prefix']}}`), '') AS `slug`,
						`breadcrumb`,
						`sl_module`,
						`sl_template`,
						IF(`link{{$this->languages('prefix')}}` != '', `link{{$this->languages('prefix')}}`, `link{{$this->lang_default['prefix']}}`) AS `link`,
						IF(`desc{{$this->languages('prefix')}}` != '', `desc{{$this->languages('prefix')}}`, `desc{{$this->lang_default['prefix']}}`) AS `desc`
						FROM `contents`
						WHERE `parent`='{$_X[$i]['id']}' AND `status`='1'
						{$order}
						", MYSQLI_ASSOC);

					if(!$_subs)
						continue;

					$this->menu[$i]['subs'] = $_subs;

						for($z=0;$z<sizeof($this->menu[$i]['subs']);$z++) {
							// Nodes
							$this->menu[$i]['subs'][$z]['nodes'] = $this->nodes($this->menu[$i]['subs'][$z]['id']);

							$order = !empty($this->menu[$i]['subs'][$z]['nodes']['sl_order']['value'])?"ORDER BY FIELD(`id`, {$this->menu[$i]['subs'][$z]['nodes']['sl_order']['value']})" :"";

							$_subs = $this->db->QueryArray("SELECT
								`id`,
								IF(`name{{$this->languages('prefix')}}` != '', `name{{$this->languages('prefix')}}`, `name{{$this->lang_default['prefix']}}`) AS `name`,
								IF(`default` = 0, IF(`slug{{$this->languages('prefix')}}` != '', `slug{{$this->languages('prefix')}}`, `slug{{$this->lang_default['prefix']}}`), '') AS `slug`,
								`breadcrumb`,
								`sl_module`,
								`sl_template`,
								IF(`link{{$this->languages('prefix')}}` != '', `link{{$this->languages('prefix')}}`, `link{{$this->lang_default['prefix']}}`) AS `link`,
								IF(`desc{{$this->languages('prefix')}}` != '', `desc{{$this->languages('prefix')}}`, `desc{{$this->lang_default['prefix']}}`) AS `desc`
								FROM `contents`
								WHERE `parent`='{$this->menu[$i]['subs'][$z]['id']}' AND `status`='1'
								{$order}
								", MYSQLI_ASSOC);

								if(!$_subs)
									continue;

								$this->menu[$i]['subs'][$z]['subs'] = $_subs;

								for($e=0;$e<sizeof($this->menu[$i]['subs'][$z]['subs']);$e++) {
									// Nodes
									$this->menu[$i]['subs'][$z]['subs'][$e]['nodes'] = $this->nodes($this->menu[$i]['subs'][$z]['subs'][$e]['id']);

									$order = !empty($this->menu[$i]['subs'][$z]['subs'][$e]['nodes']['sl_order']['value'])?"ORDER BY FIELD(`id`, {$this->menu[$i]['subs'][$z]['subs'][$e]['nodes']['sl_order']['value']})" :"";

									$_subs = $this->db->QueryArray("SELECT
										`id`,
										IF(`name{{$this->languages('prefix')}}` != '', `name{{$this->languages('prefix')}}`, `name{{$this->lang_default['prefix']}}`) AS `name`,
										IF(`default` = 0, IF(`slug{{$this->languages('prefix')}}` != '', `slug{{$this->languages('prefix')}}`, `slug{{$this->lang_default['prefix']}}`), '') AS `slug`,
										`breadcrumb`,
										`sl_module`,
										`sl_template`,
										IF(`link{{$this->languages('prefix')}}` != '', `link{{$this->languages('prefix')}}`, `link{{$this->lang_default['prefix']}}`) AS `link`,
										IF(`desc{{$this->languages('prefix')}}` != '', `desc{{$this->languages('prefix')}}`, `desc{{$this->lang_default['prefix']}}`) AS `desc`
										FROM `contents`
										WHERE `parent`='{$this->menu[$i]['subs'][$z]['subs'][$e]['id']}' AND `status`='1'
										{$order}
										", MYSQLI_ASSOC);

										if(!$_subs)
											continue;

										$this->menu[$i]['subs'][$z]['subs'][$e]['subs'] = $_subs;

										for($q=0;$q<sizeof($this->menu[$i]['subs'][$z]['subs'][$e]['subs']);$q++) {
										}
								}

						}

				}

				if($_X[$i]['sub'] == '1') {

				}

				/*
				if(@ is_array($_X[$i]['children'])) {
					for($s=0;$s<sizeof($_X[$i]['children']);$s++) {
						$this->menu[$i]['children'][$s] = $this->db->QuerySingleRowArray("SELECT
							`id`,
							IF(`name{{$this->languages('prefix')}}` != '', `name{{$this->languages('prefix')}}`, `name{{$this->lang_default['prefix']}}`) `name`,
							IF(`default` = 0, IF(`slug{{$this->languages('prefix')}}` != '', `slug{{$this->languages('prefix')}}`, `slug{{$this->lang_default['prefix']}}`), '') AS `slug`,
							`breadcrumb`,
							`sl_module`,
							`sl_template`
							FROM `contents` WHERE `id`='{$_X[$i]['children'][$s]['id']}'", MYSQLI_ASSOC);
						if(is_array($_X[$i]['children'][$s]['children'])) {
							for($z=0;$z<sizeof($_X[$i]['children'][$s]['children']);$z++) {
								$this->menu[$i]['children'][$s]['children'][$z] = $this->db->QuerySingleRowArray("SELECT
									`id`,
									IF(`name{{$this->languages('prefix')}}` != '', `name{{$this->languages('prefix')}}`, `name{{$this->lang_default['prefix']}}`) `name`,
									IF(`default` = 0, IF(`slug{{$this->languages('prefix')}}` != '', `slug{{$this->languages('prefix')}}`, `slug{{$this->lang_default['prefix']}}`), '') AS `slug`,
									`breadcrumb`,
									`sl_module`,
									`sl_template`
									FROM `contents` WHERE `id`='{$_X[$i]['children'][$s]['children'][$z]['id']}'", MYSQLI_ASSOC);
							}
						}
					}
				}
				*/

			}
		} else {
			return false;
		}

		#print_r($this->menu);
    /*
		$update = array('expiry' => strtotime('+5 minutes'), 'content' => "'".serialize($this->encode($this->menu))."'", 'name' => "'MENU'");
		$this->db->AutoInsertUpdate(
			'cache',
			$update,
			array(
				'name' => 'MENU'
				)
			);
      */

		return $this->menu;
	}

	function convert_ascii($string) {
	  $string = mb_strtolower($string,"UTF-8");
	  $string = str_replace(array('ş','ı','ü','ğ','ç','ö'),array('s','i','u','g','c','o'),$string);

	  $slug=preg_replace('/[^A-Za-z0-9-]+/', '-', $string);
	  return $slug;
	}

	public function tree($parent = 0, $children = '0', $active = 0, $self = 0, $sl_module = 0) {
		if(!empty($this->tree[$parent]))
			return $this->tree[$parent];

		if($children)
			$query[] = "`children`='".$children."'";

		if($self)
			$query[] = "`id` <> '{$self}'";

		$query[] = "`status`='1'";
		$query[] = "`parent`='".$parent."'";

		if($sl_module)
			$query[] = "`sl_module`='".$sl_module."'";

		$query = implode(" AND ",$query);

		$this->tree = $this->db->QueryArray("SELECT
			`id`,
			`slug{".$this->languages('prefix')."}` AS `slug`,
			`name{".$this->languages('prefix')."}` AS `name`,
			`status`
			FROM `contents`
			WHERE {$query}
		",MYSQLI_ASSOC);

		if(!$this->tree)
			return false;


		for($i=0;$i<sizeof($this->tree);$i++) {
			$parent = $this->tree[$i]['id'];
			$this->tree[$i]['active'] = ($active == $this->tree[$i]['id'])?1:0;

			if($this->db->QuerySingleValue("SELECT COUNT(*) FROM `contents` WHERE `parent`='".$parent."'") < 1)
				continue;

			if(!$children)
				continue;

			$query = array();
			//$query[] = "`children`='".$children."'";
			$query[] = "`parent`='".$parent."'";

			$this->tree[$i]['subs'] = $this->db->QueryArray("SELECT
				`id`,
				`slug{".$this->languages('prefix')."}` AS `slug`,
				`name{".$this->languages('prefix')."}` AS `name`,
				`status`
				FROM `contents`
				WHERE ".implode(" AND ",$query)."
			",MYSQLI_ASSOC);

			for($s=0;$s<sizeof($this->tree[$i]['subs']);$s++) {
				$parent = $this->tree[$i]['subs'][$s]['id'];
				$this->tree[$i]['subs'][$s]['active'] = ($active == $this->tree[$i]['subs'][$s]['id'])?1:0;

				if($this->db->QuerySingleValue("SELECT COUNT(*) FROM `contents` WHERE `parent`='".$parent."'") < 1)
					continue;

				$query = array();
				//$query[] = "`children`='".$children."'";
				$query[] = "`parent`='".$parent."'";

				$this->tree[$i]['subs'][$s]['subs'] = $this->db->QueryArray("SELECT
					`id`,
					`slug{".$this->languages('prefix')."}` AS `slug`,
					`name{".$this->languages('prefix')."}` AS `name`,
					`status`
					FROM `contents`
					WHERE ".implode(" AND ",$query)."
				",MYSQLI_ASSOC);

				for($z=0;$z<sizeof($this->tree[$i]['subs'][$s]['subs']);$z++) {
					$parent = $this->tree[$i]['subs'][$s]['subs'][$z]['id'];
					$this->tree[$i]['subs'][$s]['subs'][$z]['active'] = ($active == $this->tree[$i]['subs'][$s]['subs'][$z]['id'])?1:0;
				}
			}
		}

		return $this->tree;
	}

	public function tree_build(
		$data,
		$group = array('<optgroup label="%s">','</optgroup>'),
		$option = array('<option value="%s"%s>%s','</option>'),
		$first_empty = true
	) {
		$output = '';
		if($first_empty)
			$output .= sprintf($option[0], '', '', '') .$this->languages('Seçiniz'). $option[1]."\n";

		if(!$data)
			return false;

		for($i=0;$i<sizeof($data);$i++) {
			$active = ((isset($data[$i]['default']) AND $data[$i]['default'] == 1 AND !$active) OR $data[$i]['active'] == 1)?' selected':'';

			if(isset($data[$i]['subs'])) {
				$output .= sprintf($group[0], $data[$i]['name'])."\n";

				$output .= sprintf($option[0], $data[$i]['id'], $active, $data[$i]['name'])
								. $option[1]."\n";

				for($s=0;$s<sizeof($data[$i]['subs']);$s++) {
					if($data[$i]['subs']) {
						$output .= sprintf($group[0], $data[$i]['subs'][$s]['name'])."\n";
						$active = ($data[$i]['subs'][$s]['active'] == 1)?' selected':'';

						$output .= sprintf($option[0], $data[$i]['subs'][$s]['id'], $active, $data[$i]['subs'][$s]['name']) . $option[1]."\n";

						if($data[$i]['subs'][$s]['subs']) {
							for($z=0;$z<sizeof($data[$i]['subs'][$s]['subs']);$z++) {
								$active = ($data[$i]['subs'][$s]['subs'][$z]['active'] == 1)?' selected':'';

								$output .= sprintf($option[0], $data[$i]['subs'][$s]['subs'][$z]['id'], $active, $data[$i]['subs'][$s]['subs'][$z]['name']) . $option[1]."\n";
							}
						}
						$output .= $group[1]."\n";
					} else {
						$output .= sprintf($option[0], $data[$i]['subs'][$s]['id'], $active, $data[$i]['subs'][$s]['name']) . $option[1]."\n";
					}
				}

				$output .= $group[1]."\n";
			} else {
				$output .= sprintf($option[0], $data[$i]['id'], $active, $data[$i]['name']) . $option[1]."\n";
			}
		}

		return $output;
	}
/*
	public function _getallheaders() {
		if (!function_exists('getallheaders')) {
			$headers = [];

			foreach ($_SERVER as $name => $value) {
				if (substr($name, 0, 5) == 'HTTP_') {
					$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
				}
			}

			return $headers;
		} else {
			return getallheaders();
		}
	}
*/

	function ucwords_tr($string) {
		$sonuc='';
		$chars=explode(" ", $string);

		foreach ($chars as $string_flat){
			$string_length = strlen($string_flat);
			$first_char = mb_substr($string_flat,0,1,'UTF-8');

		if($first_char=='Ç' or $first_char=='ç'){
			$first_char='Ç';
		}elseif ($first_char=='Ğ' or $first_char=='ğ') {
			$first_char='Ğ';
		}elseif($first_char=='I' or $first_char=='ı'){
			$first_char='I';
		}elseif ($first_char=='İ' or $first_char=='i'){
			$first_char='İ';
		}elseif ($first_char=='Ö' or $first_char=='ö'){
			$first_char='Ö';
		}elseif ($first_char=='Ş' or $first_char=='ş'){
			$first_char='Ş';
		}elseif ($first_char=='Ü' or $first_char=='ü'){
			$first_char='Ü';
		}else{
			$first_char=strtoupper($first_char);
		}

		$others=mb_substr($string_flat,1,$string_length,'UTF-8');
		$sonuc.=$first_char.$this->tolower($others).' ';

		}

		$son=trim(str_replace('  ', ' ', $sonuc));
		return $son;
	}

	function tolower($string){
		$string=str_replace('Ç', 'ç', $string);
		$string=str_replace('Ğ', 'ğ', $string);
		$string=str_replace('I', 'ı', $string);
		$string=str_replace('İ', 'i', $string);
		$string=str_replace('Ö', 'ö', $string);
		$string=str_replace('Ş', 'ş', $string);
		$string=str_replace('Ü', 'ü', $string);
		$string=strtolower($string);

		return $string;
	}

	public function start($module = '') {
		if($module) {
			include(PB .DS. $module);
		}

		if(!$module) {
			$this->rules();

			$rule = @ (string) $this->rules[1];

			if(isset($this->modules[$rule])) {
				$this->module = $this->modules[$rule];
				include(PB . DS . 'modules' . DS . $this->modules[$rule]['file']);
			} else {
				$this->module = $this->modules['default'];
				include(PB . DS . 'modules' . DS . $this->modules['default']['file']);
			}
		}
	}

	public function registerModule($module) {
		$this->modules[$module['slug']] = $module;
	}

	public function rules() {
		if(isset($this->get['rewrite'])) {
			$rule = $this->get['rewrite']; // Receive rules
		} else {
			$rule = $this->get['rewrite'] = false;
		}
		// explode them!
		$this->rules = explode("/", $rule);
		$this->smarty->assign('rules',$this->rules);

		return $this->rules;
	}

	public function loop($i) {
		if($i >= $this->loop)
			return false;
		else
			return true;
	}

	function createslug($string) {
	  $string = mb_strtolower($string,"UTF-8");
	  $string = str_replace(array('ş','ı','ü','ğ','ç','ö'),array('s','i','u','g','c','o'),$string);

	  $slug=preg_replace('/[^A-Za-z0-9-]+/', '-', $string);
	  return $slug;
	}

	public function meta(&$array) {
		if(!empty($array['breadcrumb'])) {
			$canonical = array_map(function($d){
				if(!empty($d['permalink']))
					return $d['permalink'];
			},$array['breadcrumb']);

			$canonical = implode('/',array_filter($canonical));
		}

		//for($i=0;$i<sizeof($array['breadcrumb']);$i++)
		$meta = array(
			'permalink' => @$array['permalink'],
			'title' => (!empty($array['title'])?$array['title']:$array['name']),
			'desc' => $array['desc'],
			'published' => $array['published'],
			'modified' => $array['modified'],
			'canonical' => @$canonical
		);

		$this->smarty->assign('meta',$meta);
		return $meta;
	}

	public function array_map_recursive($callback, $array) {
	  $func = function ($item) use (&$func, &$callback) {
	    return is_array($item) ? array_map($func, $item) : call_user_func($callback, $item);
	  };

	  return array_map($func, $array);
	}

	public function alert($options, $type = 'set') {
		switch ($type) {
			case 'set':
				$this->alert_variables[] = $options;
			break;
			case 'popup':
				$options['redirect'] = (!empty($options['redirect']))?
				'location.href = "'.$options['redirect'].'";'
				:'';

				$options['options'] = 'swal("'.$options['title'].'", "'.$options['msg'].'", "'.$options['alert'].'") .then((value) => { '.$options['redirect'].' });';
				$options['type'] = 'POPUP';
				$this->alert_variables[] = $options;
			break;
			case 'kill':
				$this->alert_variables = array();
				$_SESSION['alert_variables'] = '';
			break;
			case 'read':
				if(!empty($_SESSION['alert_variables'])) {
					$this->alert_variables = (array) $this->decode(unserialize($_SESSION['alert_variables']));
				}
			break;
			case 'get':
				return $this->alert_variables;
			break;
		}

		if(!empty($this->alert_variables)) {
			$_SESSION['alert_variables'] = serialize($this->encode($this->alert_variables));
		}
	}

	function isSecure() {
	  return
	    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
	    || $_SERVER['SERVER_PORT'] == 443;
	}

	function generateRandomString($length = 10) {
	    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }
	    return $randomString;
	}

	function randomPassword() {
	    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
	    $pass = array(); //remember to declare $pass as an array
	    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
	    for ($i = 0; $i < 8; $i++) {
	        $n = rand(0, $alphaLength);
	        $pass[] = $alphabet[$n];
	    }
	    return implode($pass); //turn the array into a string
	}

	public static function RemoveExtension($strName) {
		$ext = strrchr($strName, '.');
		if($ext !== false) {
			$strName = substr($strName, 0, -strlen($ext));
		}

		return $strName;
	}

	public function devicetype() {
		if(@$this->devicetype)
			return $this->devicetype;
		else {
			$useragent					= $_SERVER['HTTP_USER_AGENT'];
			$this->devicedetect	= new Mobile_Detect;

			return $this->devicetype = ($this->devicedetect->isMobile() ? ($this->devicedetect->isTablet() ? 'tablet' : 'phone') : 'computer');
		}
	}

	public function GetIP() {
		if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
		  $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
		}
		return $_SERVER['REMOTE_ADDR'];
	}

	function String2Stars($string='',$first=0,$last=0,$rep='*') {
		$begin  = substr($string,0,$first);
		$middle = str_repeat($rep,strlen(substr($string,$first,$last)));
		$end    = substr($string, -4);//substr($string,$last);
		$stars  = $begin.'********'.$end;
		return $stars;
	}

	public function hash($str = '') {
		if(!$str)
			$str = uniqid('Silüet_SL');

		return hash('ripemd160', $this->settings['key'] . $str);
	}

	public function scripts($script, $ready = 0) {
		if(!empty($script)){
			switch ($ready) {
				case '1':
					$this->scripts[1][] = $script;
				break;

				default:
					$this->scripts[0][] = $script;
				break;
			}
		}

		return str_replace(
			array("\n","\t"," "),
			array('','',''),
			$this->scripts
		);
	}

	public function IsImage($str,$mime=true) {
		if($mime) {
			return true;
		} else {
			$ext = strtolower($this->GetExtension($str));
			if(
				$ext == 'jpg'	||
				$ext == 'jpeg'	||
				$ext == 'gif'	||
				$ext == 'bmp'	||
				$ext == 'png'	||
				$ext == 'jpe'	||
				$ext == 'svg'	||
				$ext == 'svgz'
			)
				return true;
			else
				return false;
		}
	}
	public static function GetExtension($strName) {
		if(is_string($strName)) {
			$strName = explode(".", $strName);
			return @end($strName);
		} else
			return false;
	}

	/*
	* Returns last measured duration (time between TimerStart and TimerStop)
	*
	* @param integer $decimals (Optional) The number of decimal places to show
	* @return Float Microseconds elapsed
	*/
	public function TimerDuration($decimals = 4) {
		return number_format($this->time_diff, $decimals);
	}
	/*
	* Starts time measurement (in microseconds)
	*/
	public function TimerStart() {
		$parts = explode(" ", microtime());
		$this->time_diff = 0;
		$this->time_start = $parts[1].substr($parts[0],1);
	}

	public function notfound() {
		header("HTTP/1.0 404 Not Found", true, 404);

		if(!$this->smarty->templateExists('404.html'))
	    $template = PB .DS. 'templates' .DS. 'system' .DS. '404.html';
	  else
	    $template = '404.html';

	  $this->smarty->display($template);
	  exit;
	}

	/*
	* Stops time measurement (in microseconds)
	*/
	public function TimerStop() {
		$parts  = explode(" ", microtime());
		$time_stop = $parts[1].substr($parts[0],1);
		$this->time_diff  = ($time_stop - $this->time_start);
		$this->time_start = 0;
	}

	/*
	* Read dir
	*/
	public function dir($directory=false,$dir=false,$file=false,$ext=false) {
		$files = false;
		if($directory AND is_dir($directory)) {
			$dirhandler = opendir($directory);
			$nofiles=0;
			//$file = readdir($dirhandler);

			while (false !== ($filename = readdir($dirhandler))) {
				if ($filename != '.' && $filename != '..') {
					if ($nofiles>$this->loop)
						break;

					$action = (($dir))?'dir':((($file))?'file':'');
					switch($action) {
						case 'dir' : (is_dir($directory.DS.$filename))?$files[$nofiles] = $filename:false; break;
						case 'file' : (is_file($directory.DS.$filename))?
							(($ext))?
								(($this->GetExtension($filename) == $ext))?$files[$nofiles] = $filename:false
							:$files[$nofiles] = $filename:false;
						break;
						default : $files[$nofiles] = $filename;
					}
					if(isset($files[$nofiles]))
						$nofiles++;
				}
			}
			closedir($dirhandler);
		} else {
			return false;
		}
		return $files;
	}
	/*
	* Call with full file path without DOCUMENT_ROOT;
	*/
	public function VerifyFileName($file) {
		$i = 0;
		$fileName = pathinfo($file,PATHINFO_FILENAME);
		$fileExtension = pathinfo($file, PATHINFO_EXTENSION);

		$_file = $file;

		while(file_exists(PB.$file)) {
			$file = (string)str_replace($fileName.'.'.$fileExtension,$fileName.$i.'.'.$fileExtension,$_file);
			$i++;
		}
		return $file;
	}

	/*
	* Dosya silme işlemini güvenli şekilde gerçekleştirir, sadece izin verilen UP klasörü içinde işlem gerçekleştirir.
	* UP_SRV tanımlı olduğu taktirde ilgili sunucuya bağlanarak işlem gerçekleştirir.
	*/
	public function rm($file,$dir=false) {
		if($dir) {
			return (@unlink(PB .DS. UP .DS. $dir .DS. $file))? true: false;
		} else {
			$file = str_replace(DS.UP.DS,'',str_replace('/',DS,str_replace('\\',DS,$file)));
			return (@unlink(PB .DS. UP .DS. $file))? true: false;
		}
	}

	public function RMDir($directory,$empty=false) {
		if(is_dir($directory)) {
			if(substr($directory,-1) == "/") {
				$directory = substr($directory,0,-1);
			}

			if(!file_exists($directory) || !is_dir($directory)) {
				return false;
			} elseif(!is_readable($directory)) {
				return false;
			} else {
				$directoryHandle = opendir($directory);

				while ($contents = readdir($directoryHandle)) {
					if($contents != '.' && $contents != '..') {
						$path = $directory . "/" . $contents;

						if(is_dir($path)) {
							$this->RMDir($path);
						} else {
							unlink($path);
						}
					}
				}

				closedir($directoryHandle);

				if($empty == false) {
					if(!rmdir($directory)) {
						return false;
					}
				}
				return true;
			}
		} else
			return false;
	}

	/*
	* Dosya doğrulaması gerçekleştirir UP_SRV tanımlandığı taktirde ilgili sunucuya bağlanarak kontrol gerçekleştirilir.
	*/
	public function isFile($file,$dir=false) {
		if($dir) {
			return ((is_file(PB .DS. UP .DS. $dir .DS. $file)))? true: false;
		} else {
			//$file = str_replace('/',DS,str_replace('\\',DS,$file));
			return ((is_file(PB .DS. $file)))? true: false;
		}
	}
	/*
	* Güvenli bir şekilde dosya taşıma işlemi sağlar, işlem sadece izin verilen UP klasörü içinde gerçekleştirilir.
	* UP_SRV tanımlandığı taktırde işlemi ilgili UP_SRV sunucusunda gerçekleştirir.
	*/
	public function mv($arr1,$arr2) {
		return (@rename(PB .DS. UP .DS. $arr1[1] .DS. $arr1[0],PB .DS. UP .DS. $arr2[1] .DS. $arr2[0]))? true: false;
	}

	public function clearUpload($image) {
		return str_replace(
			array('/uploads/'),
			array('/'),
			$image
		);
	}

	public function encode($str) {
		if(is_array($str)) {
			$str_keys = array_keys($str);
			for($i=0;$i<sizeof($str);$i++) {
				if(is_array($str[$str_keys[$i]])) {
					$arr[$str_keys[$i]] = $this->encode($str[$str_keys[$i]]);
				} else {
					$arr[$str_keys[$i]] = base64_encode($str[$str_keys[$i]]);
				}
				if($i>$this->loop+500)
					break;
			}
		} else {
			$arr = base64_encode($str);
		}
		if(isset($arr))
			return $arr;
		else
			return false;
	}

	private function decode_helper($str) {
		return str_replace("\'", "'", $str);
	}

	public function decode($str, $strict=false) {
		if(is_array($str)) {
			$str_keys = array_keys($str);
			for($i=0;$i<sizeof($str);$i++) {
				if(!empty($str[$str_keys[$i]])) {
					if(is_array($str[$str_keys[$i]])) {
						$arr[$str_keys[$i]] = $this->decode($str[$str_keys[$i]]);
					} else {
						if($strict) {
							if($this->IsBase64($str))
								$arr[$str_keys[$i]] = $this->decode_helper( base64_decode($str[$str_keys[$i]]) );
							else
								$arr[$str_keys[$i]] = $str[$str_keys[$i]];
						} else {
							$arr[$str_keys[$i]] = $this->decode_helper( base64_decode($str[$str_keys[$i]]) );
						}
					}
				} elseif(!is_array($str[$str_keys[$i]]))
					$arr[$str_keys[$i]] = '';

				if($i>$this->loop+500)
					break;
			}
		} else {
			if($strict) {
				if($this->IsBase64($str))
					$arr = base64_decode($str);
				else
					$arr = $str;
			} else {
				$arr = $this->decode_helper( base64_decode($str) );
			}
		}
		if(isset($arr))
			return $arr;
		else
			return false;
	}

	static public function IsBase64(&$data) {
		if(@preg_match( '%^[a-zA-Z0-9/+]*={0,2}$%',$data))
			return true;
		else
			return false;
	}

	static public function get($search,$reference) {
		if(!is_array($reference))
			return (isset($reference[$search]))? $reference[$search]: false;
		else {
			//print_r($reference);
			for($i=0;$i<sizeof($reference);$i++) {
				if(isset($reference[$i][$search])) {
					return $reference[$i][$search];
					break;
				}
			}
			return false;
		}
	}

	public function cron($options) {
		$data = $this->db->Query("INSERT INTO `cronjobs` VALUES
	    (NULL,
	      '".$options['key']."',
				'".serialize($options['options'])."',
	      '{$options['value']}',
	      '{$options['type']}',
	      '{$options['status']}',
				'{$options['response']}',
				".time()."
	    )
	    ");

			return $data ? true:false;
	}

	public function generatepassword(){
		return bin2hex(openssl_random_pseudo_bytes(4));
	}

	public function resetpassword($id = false) {
		$email = $this->db->QuerySingleValue("SELECT `email` FROM `users` WHERE `id` = {$id}");

		if($id) {
			$password = $this->generatepassword();

      $this->cron(
        array(
          'key' => $id,
          'options' => array('password' => $password, 'email' => $email),
          'status' => 1,
          'type' => 'userreset'
        )
      );

      $update = array();
      $update[] = "`password`='".md5($password)."'";
			$update = implode(',',$update);

      $c = $this->db->Query("UPDATE `users` SET
      	{$update}
        WHERE `id` = {$id}");

			return $c;
		}
	}

	public function auth($alert = false) {
		/*
		* Check, Is user still has session
		*/
		if(empty($this->cookie)) {
			$this->logout();

			if($alert)
				$this->alert(array(self::ALERT_WARNING, self::languages('LOGIN_ERROR')));

			return false;
		} else {
			// $this->uid = $this->db->QuerySingleValue(
			// 	"SELECT `uid` FROM `sessions` WHERE
			// 	`session`='".$this->cookie."' AND
			// 	`expiry` > ".time()."
			// 	");
			$this->user_session = $this->db->QuerySingleRowArray(
				"SELECT * FROM `sessions` WHERE
				`session`='".$this->cookie."' AND
				`expiry` > ".time()."
				");

      $this->uid = $this->user_session['uid'];
      $this->user_session['variables'] = json_decode($this->user_session['variables'], true);

				//`ip`='".$this->GetIP()."' AND

			if(is_numeric($this->uid)) {
				setcookie(
				  $this->settings['cookie']['name'],
				  $this->cookie,
				  $this->cookie_timeout,
					'/', '', true, true
				);

				$this->db->Query("UPDATE `sessions` SET `expiry`='".$this->cookie_timeout."'
					WHERE `id`='".$this->uid."'");
				$this->user();

				return true;
			} else {
				$this->logout();

				if($alert)
					$this->alert(array(self::ALERT_WARNING, $this->languages('LOGIN_AGAIN')));

				return false;
			}
		}
	}

	public function sendMail($options)
	{
		$from['email'] = (!empty($options['from']['email'])) ? $options['from']['email'] : $this->settings['smtp']['from']['email'];
		$from['name'] = (!empty($options['from']['name'])) ? $options['from']['name'] : $this->settings['smtp']['from']['name'];


		$mail = new PHPMailer;
		$mail->ClearAllRecipients();
		$mail->IsSMTP();
		$mail->SMTPAuth = true;
		$mail->Host = $this->settings['smtp']['host'];
		$mail->Port = $this->settings['smtp']['port'];
		$mail->SMTPSecure = $this->settings['smtp']['secure'];
		$mail->Username = $this->settings['smtp']['username'];
		$mail->Password = $this->settings['smtp']['password'];
		$mail->SetFrom($from['email'], $from['name']);
		$mail->CharSet = 'UTF-8';
		$mail->debug = 2;

		for ($i = 0; $i < sizeof($options['recipients']); $i++) {
			$mail->addAddress($options['recipients'][$i][0], $options['recipients'][$i][1]);
		}

		$mail->Subject = $options['subject'];

		if (isset($options['attachments']))
			for ($i = 0; $i < sizeof($options['attachments']); $i++)
				$mail->addAttachment($options['attachments'][$i][0], $options['attachments'][$i][1]);

		if (isset($options['stringAttachments']))
			for ($i = 0; $i < sizeof($options['stringAttachments']); $i++)
				$mail->AddStringAttachment($options['stringAttachments'][$i][0], $options['stringAttachments'][$i][1]);

		$mail->msgHTML($options['body']);

		if ($mail->send()) {
			return true;
		} else {
			return false;
		}
	}

  function session(): void {
    // error_reporting(E_ALL);
		session_set_cookie_params([
		    'lifetime' => $this->settings['cookie']['lifetime'],
		    'path' => $this->settings['cookie']['path'],
		    'domain' => $this->settings['domain']['host'],
		    'secure' => true,
		    'httponly' => true,
		    'samesite' => $this->settings['cookie']['samesite']
		]);

		session_name($this->settings['cookie']['session']);
		session_start();
	}

	public function login($email,$password) {
		$recaptcha = new \ReCaptcha\ReCaptcha($this->settings['gcaptchaV2']['secret']);

		$resp = $recaptcha
											->verify($this->post['g-recaptcha-response'], $this->GetIP());

    if ($resp->isSuccess()) {
			if(!empty($email) AND !empty($password)) {
				$email = mysqli_real_escape_string($this->db->mysql_link, $email);
				$password = mysqli_real_escape_string($this->db->mysql_link, $password);

		    $this->uid = $this->db->QuerySingleValue("SELECT `id` FROM `users`
					WHERE
					`email`='".$email."' AND `status`='1' AND `password` = '".md5($password)."'
					");

        if($this->uid > 0) {
					#$this->db->Query("DELETE FROM `user_sessions` WHERE `uid`='".$this->uid."'");

					echo $x = md5($this->uid.time());

          setcookie(
					  $this->settings['cookie']['name'],
					  $x,
					  $this->cookie_timeout,
						'', '', true, true
					);

		      $this->db->Query("UPDATE `users` SET `last_login`='".date("Y-m-d H:i:s")."' WHERE `id`='".$this->uid."'");

          $variables = json_encode([]);

          if(isset($this->post['username']))
            $variables = json_encode(array('username' => $this->post['username']), JSON_UNESCAPED_UNICODE);

          $this->db->Query("INSERT INTO
            `sessions` (`id`,`uid`,`ip`,`time`,`expiry`,`session`,`variables`) VALUES
            (NULL, '".$this->uid."', '".$this->GetIP()."', '".time()."','".$this->cookie_timeout."', '".$x."','{$variables}')
          ");

          // $this->db->Query("INSERT INTO
					// 	`sessions` (`id`,`uid`,`ip`,`time`,`expiry`,`session`) VALUES
					// 	(NULL, '".$this->uid."', '".$this->GetIP()."', '".time()."','".$this->cookie_timeout."', '".$this->cookie."')
					// ");

          $this->user();

					return true;
				} else {
					$this->alert(array(self::ALERT_WARNING, self::languages('LOGIN_PASS_OR_USER')));

					return false;
				}
			} else {
				$this->alert(array(self::ALERT_WARNING, self::languages('LOGIN_EMPTY_SUBMIT')));

				return false;
			}
		}

		$this->alert(array(self::ALERT_WARNING, 'Güvenlik adımını tamamlayınız.'));
		return false;

	}

	public function user() {
		if(empty($this->user) AND $this->uid > 0)
			$this->user = $this->db->QuerySingleRowArray(
				"SELECT `u`.*, `ug`.`level` FROM `users` AS `u` INNER JOIN `user_groups` AS `ug`
				ON `ug`.`id` = `u`.`group`
				WHERE `u`.`id`='".$this->uid."'",MYSQLI_ASSOC
			);

    if($this->uid > 0) {
			$this->smarty->assign('user',$this->user);
			return $this->user;
		} else
			return false;
	}

	public function logout() {
		setcookie(
			$this->settings['cookie']['name'],
			'',
			time() - (10 * 365 * 24 * 60 * 60),
			'/', '', true, true
		);

		$c = $this->db->Query("DELETE FROM `sessions` WHERE `session`='".$this->cookie."'");

		return true;
	}

	public function covid() {
		$json = $this->db->QuerySingleValue("SELECT `content` FROM `cache` WHERE `name`='COVID' AND `expiry` > UNIX_TIMESTAMP() LIMIT 1");

		if(empty($json) OR $_GET['g'] == '1') {
			$html = file_get_contents('https://covid19.saglik.gov.tr/');

			$dom = new DOMDocument;
			$dom->loadHTML($html);
			$finder = new DomXPath($dom);

			$a = $json = $finder->query('//script')->item(19)->nodeValue;
			$json = 			str_replace(
				array('//<![CDATA[', 'var sondurumjson = ', '//]]>',';','var haftalikdurumjson = '),
				array('','','','','_SEPERATOR_'),
				$json
			);
			$json = explode('_SEPERATOR_', $json)[0];

			//$json = preg_match('/var haftalikdurumjson = (.*)/', $json, $output_array);

			//$json = json_decode($output_array[1], true);

			//$json = json_encode($json);

			/*
			if($_GET['g'] == '1') {

				echo $json;
				exit;
			}
			*/

			/*
			$html = file_get_contents('https://covid19.saglik.gov.tr/');
			preg_match_all('/\/\/<!\[CDATA\[\nvar sondurumjson = (.*)\/\/\]\]>/suUXgmi', $html, $output_array);
			var_dump($output_array);
			*/

			$this->db->AutoInsertUpdate(
				'cache',
				array(
					'expiry' => strtotime('+1 hours'),
					'content' => "'{$json}'",
					'name' => "'COVID'"
				),
				array(
					'name' => "'COVID'"
				)
			);
		}

		$json = json_decode($json, true)[0];
		/*
[{"tarih":"10 - 16 TEMMUZ 2021","test_sayisi":"1.608.670","vaka_sayisi":"43.609","hasta_sayisi":"3.748","vefat_sayisi":"295","iyilesen_sayisi":"36.377","toplam_vaka_sayisi":"5.514.373","toplam_vefat_sayisi":"50.450","ortalama_agir_hasta_sayisi":"552","hastalarda_zaturre_oran":"4.7","yatak_doluluk_orani":"48.9","eriskin_yogun_bakim_doluluk_orani":"62.1","ventilator_doluluk_orani":"26.3"}]
		*/

		$statCovid = array(
		  'status' => 'success',
		  'response' => $json
			  /*
		  array(
		    'update' => date('Y-m-d'),
		    'daily' => array(
		      'test' => $json['test_sayisi'],
		      'cases' => $json['vaka_sayisi'],
		      'death' => $json['gunluk_vefat'],
		      'recovered' => $json['gunluk_iyilesen'],
		    ),

		    'total' => array(
					'test' => $json['toplam_test'],
		      'cases' => $json['toplam_vaka'],
		      'death' => $json['toplam_vefat'],
		      'recovered' => $json['toplam_iyilesen'],
		      'zature_orani' => $json['hastalarda_zaturre_oran'],
		      'intubated' => $json['agir_hasta_sayisi']
		    )
		  )
		  */
		);

		$this->smarty->assign('statCovid', $statCovid);

		$researches = file_get_contents(PB . '/uploads/covid.csv');
		$researches = explode("\n", $researches);

		for($i=0;$i<sizeof($researches);$i++) {
		  $researches[$i] = explode(';', $researches[$i]);

		  if(empty($researches[$i][0]) OR $researches[$i][0] == '-')
		    $researches[$i][0] = $researches[$i-1][0];

				/*
		  $date = explode('/', $researches[$i][0]);

			$researches[$i]['date_clean'] = date('Ymd', strtotime("20$date[2]/$date[0]/$date[1]"));
		  $researches[$i]['date'] = date('d/m/Y', strtotime("20$date[2]/$date[0]/$date[1]"));
			26.05.2021
			*/

			$date = explode('.', $researches[$i][0]);
			$researches[$i]['date_clean'] = date('Ymd', strtotime("20$date[2]/$date[1]/$date[0]"));
		  $researches[$i]['date'] = $researches[$i][0];///date('d/m/Y', strtotime("20$date[2]/$date[1]/$date[0]"));
		}

		$this->smarty->assign('researches', $researches);
	}

	public function isAdmin() {
		if(isset($_COOKIE['SLSESS']) AND !empty($_COOKIE['SLSESS']))
			return true;

		return false;
	}

	public function __destruct() {
		if($this->debug()) {
			$this->TimerStop();

			if($this->nocomment()) {
				$str = '
				<!--
				Siluet 3 Debug Mode
				PHP Duration: '.$this->TimerDuration().'
				-->
				';
				echo $str;
			}
		} else {

		}
	}

}
