<?php
namespace master {
  use Smarty;
  use Smarty_Security;

	class engine extends Smarty {}
	class engine_Security extends Smarty_Security {}

	class View {
    protected
      $engine;

    public function __construct() {
      $this->engine = new engine;
    }

    public function init($view_config) {
      $this->view_config = $view_config;

			$sl_security_policy = new engine_Security($this->engine);
			// disable all PHP functions
			$sl_security_policy->php_functions = array('mb_substr','nl2br','sizeof','in_array','strip_tags','round','empty','isset','is_array','is_numeric','is_string','count');
			// remove PHP tags
			$sl_security_policy->php_handling = engine::PHP_PASSTHRU;
      $sl_security_policy->php_modifiers = array('mb_substr','nl2br','date','strip_tags','str_replace','round','implode','substr', 'print_r','var_dump','json_encode','json_decode','number_format');
			// allow everthing as modifier
			$sl_security_policy->modifiers = array();
			// enable security
      $sl_security_policy->secure_dir = [$_SERVER['DOCUMENT_ROOT'] . '/uploads/'];
			$this->engine->enableSecurity($sl_security_policy);

			$this->engine->auto_literal = false;
			$this->engine->left_delimiter = "{%";
      $this->engine->right_delimiter = "%}";
			$this->engine->debugging = false;
      $this->engine->error_reporting = E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED;

			require(PATH .DS. 'system' .DS. 'modules' .DS. 'incs' .DS. 'smarty.plugins.php');

      #$navs = new Navigation();
      #var_dump($navs);

      foreach (glob(SYSTEM .DS. 'plugins' .DS. "*.php") as $filename) {
        include $filename;
      }

      if(!is_dir($this->view_config['cache']['path'] . 'sl.view.complies'))
        mkdir($this->view_config['cache']['path'] . 'sl.view.complies', true);

      if(!is_dir($this->view_config['cache']['path'] . 'sl.view.cache'))
        mkdir($this->view_config['cache']['path'] . 'sl.view.cache', true);

      $this->engine
  			->setTemplateDir(array(
  					'default' => $this->view_config['templates']['path'] . $this->view_config['templates']['theme'],
  			    'system' => $this->view_config['templates']['path'] . $this->view_config['templates']['default']
  			))
  			->setCompileDir($this->view_config['cache']['path'] . 'sl.view.complies')
  			->setCacheDir($this->view_config['cache']['path'] . 'sl.view.cache');
        //->secure_dir = [$this->view_config['templates']['path'] . $this->view_config['templates']['theme'] . '/assets/svg'];
      #->addPluginsDir(SYSTEM .DS. 'plugins');

			if(false) {
        $this->engine->clearAllCache();

        $this->view_config['asset_version'] = time();
        $this->engine->debugging = false;
				$this->engine->compile_check = true;
				$this->engine->force_compile = false;
			} else {
				$this->engine->compile_check = true;
				$this->engine->force_compile = false;
			}

      #var_dump($this->engine->cacheLifetime());
      #$this->engine->setCaching(smarty::CACHING_LIFETIME_CURRENT);
      #$this->engine->setCaching(3600);

      $this->append('prefix', $this->view_config['prefix']);
      $this->append('settings', $this->view_config);
    }
    /*
    * View assign a value with a key
    * scope = parent, root, global
    */
    public function append($key, $value, $scope = 'root') {
      $this->engine->assign($key, $value);
    }

    public function isCached($file, $cacheId) {
      if(strpos($file, 'extends') !== FALSE) {
        $file = implode('|', array_map(function($e){ return "{$e}.tpl"; },
          explode('|', str_replace('extends:', '', $file))
        ));

        return $this->engine->isCached("extends:{$file}", $cacheId);
      } else {
        if(strpos($file, '.tpl') !== TRUE)
          $file = $file . '.tpl';

        return $this->engine->isCached($file, $cacheId);
      }
    }

    public function setpath($path) {
      $this->engine
			->setTemplateDir(array(
					'default' => $path,
			));
    }

    public function output($file, $cache = false) {
      $fetch = $this->engine->fetch($file, $cache);

      if(strpos($fetch,'<head>') !== FALSE) {
        $fetch = str_replace(
          '<head>',
          '<head>
    <link rel="developer" href="https://github.com/orkank">',
          $fetch
        );
      }

      echo $fetch;
    }

    public function fetch($file, $cache = false) {
      if(strpos($file, '.tpl') == FALSE)
        $file = $file . '.tpl';

      return $this->engine->fetch($file, $cache);
    }

    public static function isDebug() {
      if(MODE == 'DEV') {
        return true;
      } else {
        return false;
      }
    }
  }
}
