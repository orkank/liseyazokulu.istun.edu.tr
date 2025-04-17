<?php
namespace master {
	use \TRegx\SafeRegex\preg;

	class Modules {
		public
		$default,
		$module,
		$modules;

		public function __construct(&$modules) {
			for($i=0;$i<sizeof($modules);$i++) {
				$this->register($modules[$i]);
			}
		}

		public function __destruct() {
		}

		public function file($request) {
			if(isset($this->modules) AND count($this->modules) > 0) {
				$this->request = $request;

				for($i=0;$i<sizeof($this->modules);$i++) {
					//$request = str_replace('.php', '', $request);

					if(
						!empty($this->modules[$i]['regex']) AND
						!empty($request) AND
						\preg_match($this->modules[$i]['regex'], $request, $matches, PREG_UNMATCHED_AS_NULL)
					) {
						if(
								(
									isset($this->modules[$i]['callback']) AND
									is_callable($this->modules[$i]['callback']) AND
									$this->modules[$i]['callback']($this->modules[$i])
								) OR
								!isset($this->modules[$i]['callback'])
							) {
								$this->module = &$this->modules[$i];
								return $this->modules[$i]['file'];
							}
					}

					if(
						!empty($this->modules[$i]['prefix']) AND
						!empty($request) AND
						$this->modules[$i]['prefix'] == $request
					) {
						if(
								(
									isset($this->modules[$i]['callback']) AND
									is_callable($this->modules[$i]['callback']) AND
									$this->modules[$i]['callback']($this->modules[$i])
								) OR
								!isset($this->modules[$i]['callback'])
							) {
								$this->module = &$this->modules[$i];
								return $this->modules[$i]['file'];
							}
					}

				}
			}

			//$this->default['callback']($request);
			return $this->default['file'];
		}

		public function register($module) {
			if(
				is_file($module['file']) AND
				($module['prefix'] != NULL OR $module['regex'] != '')
			) {
				$this->modules[] = $module;
			} elseif(is_file($module['file'])) {
				$this->default = $module;
			} else {
				//throw new Exception("[FATAL] Module cannot be load, {$module['prefix']}", '107');
			}
		}

	}
}
