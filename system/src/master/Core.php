<?php
/**
*	Original File Name	:	class.sl.php
*	Version							:	1.0
*	Created							:	Wed, 14-08-2020
*	Modified						:	Wed, 14-08-2020
*	Copyright						:	Copyright (C) 2020 Orkan KÖYLÜ
*	Author							:	Orkan KÖYLÜ
*/

namespace master;

use master\Modules;
use master\View;
use master\User as user;
use PDO;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleRetry\GuzzleRetryMiddleware;

class Core {
	// Public variables
	public
		$version = '1.00',
		$settings,
		$get,
		$view,
		$modules,
    $requests = [],
		$prefix = 'tr',
		$post;

		private $time_start     =   0;
    private $time_end       =   0;
    private $time           =   0;

	// Private variables
	private
		$default_headers = array(
			["X-Frame-Options", "SAMEORIGIN"],
			["X-Version", "SLSS 5.00"],
			["X-Version", "1.0"],
			/*
			["Cache-Control", "no-store, no-cache, must-revalidate"],
			["Access-Control-Allow-Origin", "https://static.aria0.com"],
			["Access-Control-Allow-Headers", "X-Requested-With, Content-Type, Accept, Origin, Authorization"],
			["Access-Control-Allow-Methods", "GET, POST, PUT, DELETE, PATCH, OPTIONS"]
			*/
		);

	/**
   * Static instance of self
   *
   * @var master\Core
   */
  public static $instance;

  const MIN_PHP_VERSION = '7.2';

	public function getLoadTime(){
		$this->time_end = microtime(true);
		$this->time = $this->time_end - $this->time_start;
		return "Loaded in $this->time seconds\n";
	}

	public function __construct(&$settings, $modules = false) {
    $this->time_start= microtime(true);

		if (!defined('SL')) header("Location: /404");

		$this->settings = $settings;
		$this->checkCompatibility($settings);

		$this->writeHeader($this->default_headers);
		$this->session();

		$this->cookie = new CookieManager($this->settings);

		$this->modules = new Modules($modules);

		$this->dataConfig =
		[
			'host'		 => $this->settings['database']['host'],
			'port'     => $this->settings['database']['port'],
			'username' => $this->settings['database']['user'],
			'password' => $this->settings['database']['password'],
			'database' => $this->settings['database']['prefix'] . $this->settings['database']['db']
		];

		$this->requests();

    if(isset($this->get['theme']) AND $this->get['theme'] == 'modern') {
      $this->settings['view_config']['templates']['theme'] = 'modern';
      $this->settings['view_config']['theme'] = $this->settings['domain']['static'] . '/templates/' . 'modern';
      $this->settings['templates']['theme'] = 'modern';
      $this->settings['view_config']['asset'] = $settings['domain']['static'] . '/templates/modern/assets/';
    }

		$this->view = new View();

		if($this->isAdmin()) {
			$this->view->append('editpage', true);
		}

		$this->database();

		$this->user = new user($this);

		$this->settings['view_config']['prefix'] = $this->getLanguagePrefix();
		$this->view->init($this->settings['view_config']);

		self::$instance = $this;
	}

	public static function getInstance(): object {
		return self::$instance;
  }

	public function getLang($e) {
		return $e;
	}

	public function getLanguagePrefix($str = '') {
		return $this->prefix;
	}

	public function start($settings = false): void {
		if($settings) {
			$this->settings = $settings;
		}
	}

	public function queryRaw($query, $data = []) {
		echo substr_replace($queryi,$data);
	}

	public function lastInsertId() {
		return $this->db->lastInsertId();
	}

	function placeholders($text, $count = 0, $separator = ","){
			$result = array();
			if($count > 0){
					for($x=0; $x<$count; $x++){
							$result[] = $text;
					}
			}

			return implode($separator, $result);
	}

	public function bulkInsert($table = '', $data = [], $fields = []) {
		$this->db->beginTransaction(); // also helps speed up your inserts.
		$insertValues = array();

		foreach($data as $d){
			$question_marks[] = '('  . $this->placeholders('?', sizeof($d)) . ')';
			$insertValues = array_merge($insertValues, array_values($d));
		}

		$sql = "INSERT INTO {$table} (" . implode(",", $fields ) . ") VALUES " .
		       implode(',', $question_marks);

		$stmt = $this->db->prepare($sql);
		$stmt->execute($insertValues);
		return $this->db->commit();
	}

	public function queryRollback($queries = []) {
		try {
			$this->db->beginTransaction();

			for($i=0;$i<sizeof($queries);$i++) {
				$this->db->prepare($queries[$i][0])->execute($queries[$i][1]);
			}

			$commit = $this->db->commit();

			if(!$commit) {
				$this->db->rollBack();
				return false;
			}

			return $commit;
		} catch(\Exception $e) {
			return false;
		}
	}

	public function Query($query, $data = array()) {
		$query = str_replace(array("\n"), ' ', $query);

		try {
			$result = $this->db->prepare($query)->execute($data);
			return $result;
		} catch (\Exception $e) {
			$this->saveException($e, $query);
		}

    //print_r($this->db->errorInfo());
		return false;
	}

	/**
	 * Check if a string is serialized
	 * @param string $string
	 */
	public static function isSerial($string) {
	    return (@unserialize($string) !== false);
	}

	public function QuerySingleValue($query, $data = [], $style = PDO::FETCH_ASSOC) {
		$query = str_replace(array("\n"), ' ', $query);

		try {
			$sth = $this->db->prepare($query);
			$sth->execute($data);
			$result = $sth->fetchColumn(0);

			return $result;
		} catch (\Exception $e) {
			$this->saveException($e, $query);
		}

		return false;
	}

	public function QuerySingleRowArray($query, $data = [], $style = PDO::FETCH_ASSOC) {
		$query = str_replace(array("\n"), ' ', $query);

		try {
			$sth = $this->db->prepare($query);
			$sth->execute($data);
			$result = $sth->fetch($style);

			return $result;
		} catch (\Exception $e) {
			$this->saveException($e, $query);
		}

		return false;
	}

	public function QueryArray($query, $data = [], $style = PDO::FETCH_ASSOC, $key = '') {
		$query = str_replace(array("\n"), ' ', $query);

		try {
			$sth = $this->db->prepare($query);
			$sth->execute($data);

			$result = $sth->fetchAll($style);

			if(!empty($key)) {
				$_ = [];
				for($i=0;$i<sizeof($result);$i++) {
					$_[$result[$i][$key]] = $result[$i];
				}

				$result = $_;
			}

			return $result;
		} catch (\Exception $e) {
			$this->saveException($e, $query);
		}

		return false;
	}

	public function saveException($e, $query) {
		$action = '#';

		$this->Query(
			"INSERT INTO query_errors (query,exception,action) VALUES (?,?,?)",
			array(
				$query,
				$e->getMessage(),
				$action
			)
		);
	}

	function caseConverter($keyword, $transform='lowercase') {
		$low = array('a','b','c','ç','d','e','f','g','ğ','h','ı','i','j','k','l','m','n','o','ö','p','r','s','ş','t','u','ü','v','y','z','q','w','x');
		$upp = array('A','B','C','Ç','D','E','F','G','Ğ','H','I','İ','J','K','L','M','N','O','Ö','P','R','S','Ş','T','U','Ü','V','Y','Z','Q','W','X');

		if($transform == 'uppercase' OR $transform=='u') {
			$keyword = str_replace($low, $upp, $keyword);
			$keyword = function_exists('mb_strtoupper') ? mb_strtoupper($keyword) : $keyword;
		} elseif($transform == 'lowercase' OR $transform == 'l') {
			$keyword = str_replace($upp, $low, $keyword);
			$keyword = function_exists('mb_strtolower') ? mb_strtolower($keyword) : $keyword;
		}

		return $keyword;
	}

	public function isAdmin() {
		if(isset($_COOKIE['SLSESS']) AND !empty($_COOKIE['SLSESS']))
			return true;

		return false;
	}

	public function database() {
		try {
			$this->db = new PDO("mysql:host={$this->settings['database']['host']};
				port={$this->settings['database']['port']};charset=utf8mb4;
				dbname={$this->settings['database']['db']}",
				"{$this->settings['database']['user']}",
				"{$this->settings['database']['password']}");
      $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (\Exception $e) {
			echo $e->getMessage();
			die('db error');
		}
	}

	public function identityCookie($set = false): void {
		//$this->identityCookieName = COOKIE .'_$001'. md5($_SERVER['HTTP_USER_AGENT'] . $this->IP()['IPADDR']);
		$this->identityCookieName = COOKIE .'_$001_USER_COOKIE';
		$this->identityCookieNameToken = COOKIE .'_$001_USER_TOKEN';
		$this->identityCookieValue = CookieManager::getCookie($this->identityCookieName);

		if(empty($this->identityCookieValue) AND $set === true) {
			$this->identityCookieValue = \uniqid('', true);
			$this->cookie->setCookie($this->identityCookieName, $this->identityCookieValue);
		}
	}

	public function token($token = '') {
		if(!empty($token)) {
			return ($token === $_SESSION['token']) ? true : false;
		}

		if(empty($_SESSION['token'])) {
			$_SESSION['token'] = bin2hex(random_bytes(32));
		}

		return $_SESSION['token'];

		/*
		if(!empty($str)) {
			return hash_hmac('sha256', 'SL', $this->token);
		} else {

			$this->token = $_SESSION['token'];

			return $this->token;
		}
		*/
	}

	public function IP(): array {
		if(isset($this->IP)) {
			return $this->IP;
		}

		if(isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
		  $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
		}

		if(isset($_SERVER['HTTP_X_SUCURI_CLIENTIP'])) {
		  $_SERVER["REMOTE_ADDR"] = $_SERVER['HTTP_X_SUCURI_CLIENTIP'];
		}

		$this->IP['IPADDR'] = $_SERVER['REMOTE_ADDR'];
		#$this->IP['IPADDR'] = '127.0.0.1';
		$this->IP['IPLONG'] = ip2long($this->IP['IPADDR']);

		if(empty($this->IP['IPLONG']))
			$this->IP['IPLONG'] = 1111111111;

		return $this->IP;
	}

	public function entities($str) {
		$find = array('.php');
		$repl = array('');

		return str_replace($find, $repl, htmlentities($str, ENT_QUOTES));
	}

	function ucWords($string) {
		//return mb_convert_case($string, MB_CASE_TITLE, "UTF-8");
		$result = '';
		$chars = explode(" ", $string);

		foreach ($chars as $string_flat) {
			$string_length = strlen($string_flat);
			$first_char = mb_substr($string_flat,0,1,'UTF-8');

			if($first_char=='Ç' or $first_char=='ç') {
				$first_char='Ç';
			} elseif ($first_char=='Ğ' or $first_char=='ğ') {
				$first_char='Ğ';
			} elseif($first_char=='I' or $first_char=='ı'){
				$first_char='I';
			} elseif ($first_char=='İ' or $first_char=='i'){
				$first_char='İ';
			} elseif ($first_char=='Ö' or $first_char=='ö'){
				$first_char='Ö';
			} elseif ($first_char=='Ş' or $first_char=='ş'){
				$first_char='Ş';
			} elseif ($first_char=='Ü' or $first_char=='ü'){
				$first_char='Ü';
			} else {
				$first_char=strtoupper($first_char);
			}

			$others = mb_substr($string_flat,1, $string_length, 'UTF-8');
			$result .= $first_char . $this->tolower($others).' ';

		}

		$result = trim(str_replace('  ', ' ', $result));

		return $result;
	}

	function tolower($string){
		$string = str_replace('Ç', 'ç', $string);
		$string = str_replace('Ğ', 'ğ', $string);
		$string = str_replace('I', 'ı', $string);
		$string = str_replace('İ', 'i', $string);
		$string = str_replace('Ö', 'ö', $string);
		$string = str_replace('Ş', 'ş', $string);
		$string = str_replace('Ü', 'ü', $string);

		$string = strtolower($string);

		return $string;
	}

	public function requests(): array {
		$this->post = &$_POST;
		$this->get = &$_GET;

		if(isset($this->get['rewrite'])) {
			$requests = htmlentities($this->get['rewrite'], ENT_QUOTES);

			// explode them!
			$this->requests = array_filter(explode("/", $requests));
			$i = 0;
			$this->requests[0] = (!isset($this->requests[0]) OR empty($this->requests[0])) ? 'tr' : $this->requests[0];

			switch($this->requests[0]) {
				case 'tr' :
				case 'en' :
					$this->prefix = $this->requests[0];
				break;
				default:
			}
			/*
			for(;;) {
				if(!isset($this->requests[$i])) break;
				if(strpos($this->requests[$i], '.php', 0) > 0): unset($this->requests[$i]); endif;
				$i++;
			}
			asort($this->requests);
			*/

			// NOTE: At this point remove language prefix from requests if match to any active language prefix and tell language $prefix
			if(is_object($this->view)) {
				$this->view->append('requests', $this->requests);
				$this->view->append('asset_version', time());
      }
		}

		return $this->requests ?? [];
	}

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

	public static function checkCompatibility($settings): void {
		/*
		if(!version_compare(PHP_VERSION, self::MIN_PHP_VERSION, '>=')) {
			throw new SLException("[FATAL] Silüet needs PHP version must be greater than " . self::MIN_PHP_VERSION.'.', '100');
		}
		*/

		/*
		if (!in_array($settings['database']['driver'], PDO::getAvailableDrivers(), true)) {
			throw new SLException("[FATAL] {$settings['database']['driver']} database driver needed.", '101');
		}
		*/
	}

	public function jsondecode($data, $assoc = true) {
		return json_decode($data, $assoc);
	}

	function slug($string) {
		$string = mb_strtolower($string,"UTF-8");
		$string = str_replace(array('ş','ı','ü','ğ','ç','ö'), array('s','i','u','g','c','o'),$string);

		$slug=preg_replace('/[^A-Za-z0-9-]+/', '-', $string);
		return $slug;
	}

	public static function slugify($text, string $divider = '-') {
		/*
		$slug = \Transliterator::createFromRules(
		    ':: Any-Latin;'
		    . ':: NFD;'
		    . ':: [:Nonspacing Mark:] Remove;'
		    . ':: NFC;'
		    . ':: [:Punctuation:] Remove;'
		    . ':: Lower();'
		    . '[:Separator:] > \'-\''
		)
		    ->transliterate( $text );
				return $slug;
				*/

	  // replace non letter or digits by divider
	  $text = preg_replace('~[^\pL\d]+~u', $divider, $text);

	  // transliterate
	  $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

	  // remove unwanted characters
	  $text = preg_replace('~[^-\w]+~', '', $text);

	  // trim
	  $text = trim($text, $divider);

	  // remove duplicate divider
	  $text = preg_replace('~-+~', $divider, $text);

	  // lowercase
	  $text = strtolower($text);

	  if (empty($text)) {
	    return;
	  }

	  return $text;
	}

	/*
   * Searches for $needle in the multidimensional array $haystack.
   *
   * @param mixed $needle The item to search for
   * @param array $haystack The array to search
   * @return array|bool The indices of $needle in $haystack across the
   *  various dimensions. FALSE if $needle was not found.
   */
  function recursive_array_search($needle,$haystack) {
    foreach($haystack as $key=>$value) {
      if($needle===$value) {
        return array($key);
      } else if (is_array($value) && $subkey = $this->recursive_array_search($needle,$value)) {
        array_unshift($subkey, $key);
        return $subkey;
      }
    }
  }

	function stripCarriageReturns($string) {
	    return str_replace(array("\n\r", "\n", "\r"), '', $string);
	}

	public function entitiesEncode($str) {
		return htmlentities($str, ENT_HTML5, "UTF-8");
	}

	public function entitiesDecode($str) {
		return html_entity_decode($str, ENT_HTML5, "UTF-8");
	}

	public function jsonencode($array) {
		return json_encode($array, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
		return json_encode($array, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
	}

	public function json($array) {
		$this->writeHeader(array("key" => "Content-type", "value" => "application/json"));

		if($array)
			return json_encode($array, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
	}

	public function redirect($redirect = '', $return = false, $code = 302, $replace = true, $noreturn = false): void {
		if(!$return)
			$return = $_SERVER['REQUEST_URI'];

		$return = \urlencode($return);

		if($noreturn)
			header("Location: {$redirect}", $replace, $code);
		else
			header("Location: {$redirect}?return={$return}", $replace, $code);
	}

	public function removeEX($str) {
		return preg_replace('/\\.[^.\\s]{3,4}$/', '', $str);
	}

	public function generatepass($password) {
		return password_hash($password, PASSWORD_DEFAULT, ['cost' => 16]);
	}

	static protected function passwordVerify($password, $hash) {
		return password_verify($password, $hash);
	}

	public function serveFile($filepath, $filename = NULL, $type = NULL) {
		if($type == 'B64') {
			$_filename = basename($filepath);

			if (!$filename) {
				$filename = $_filename;
			}

			$filename = base64_decode(explode('__', $_filename)[0]);
		} elseif (!$filename) {
			$filename = basename($filepath);
		}

		$mimeType = mime_content_type($filepath);

		header("Content-Transfer-Encoding: binary");
		header("Content-Length: " . filesize($filepath));
		header("Pragma: no-cache");
	  header("Expires: 0");
		header("Content-type: {$mimeType}");
		header('Content-Disposition: attachment; filename="'.$filename.'"');

		readfile($filepath);
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

	private function session(): void {
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

	public function TCNumberVerify($TCNumber) {
		if(strlen($TCNumber) == 11) {
			$step = str_split($TCNumber);
			$step1 = $step[0];
			$step2 = $step[1];
			$step3 = $step[2];
			$step4 = $step[3];
			$step5 = $step[4];
			$step6 = $step[5];
			$step7 = $step[6];
			$step8 = $step[7];
			$step9 = $step[8];
			$step10 = $step[9];
			$step11 = $step[10];

			$step10_test = fmod(($step1 + $step3 + $step5 + $step7 + $step9) * 7  - ($step2 + $step4 + $step6 + $step8), 10);
			$step11_test = fmod($step1 + $step2 + $step3 + $step4 + $step5 + $step6 + $step7 + $step8 + $step9 + $step10, 10);
		}

		if(strlen($TCNumber) != 11) {
			$response = false;
		} elseif($step1 == 0) {
			$response = false;
		} elseif(
			!is_numeric($step1) OR
			!is_numeric($step2) OR
			!is_numeric($step3) OR
			!is_numeric($step4) OR
			!is_numeric($step5) OR
			!is_numeric($step6) OR
			!is_numeric($step7) OR
			!is_numeric($step8) OR
			!is_numeric($step9) OR
			!is_numeric($step10) OR
			!is_numeric($step11)) {
				$response = false;
			} elseif($step10_test != $step10) {
				$response = false;
			} elseif($step11_test != $step11) {
				$response = false;
			} else {
				$response = true;
			}

		return 	$response;
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

	public function __destruct() {
	}
}
