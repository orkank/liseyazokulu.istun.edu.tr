<?php
namespace master;
use master\Core;

class User {
	// Public variables
	public
    $info,
		$module = [],
		$groups;

	// Private variables
	protected
		$auth = false,
		$permission = [],
    $permissions = [];

	public function __construct($core) {
		$this->core = $core;
		#$this->auth();
  }

	public function groups(): array {
		/*
		if(!empty($this->groups))
			return $this->groups;

		$this->groups = $this->db->get('user_groups');

		return $this->groups ?? [];
		*/
	}

	public function read(): bool {
		/*
		if(empty($this->module))
			return false;
			*/

		if($this->permission['read'] === true) {
			return true;
		}

		return false;
	}

	public function write(): bool {
		/*
		if(empty($this->module))
			return false;
			*/

		if($this->permission['write'] === 1) {
			return true;
		}

		return false;
	}

	public function permissions($module = false, $key = false): array {
		$this->permission = [
			"read" => false,
			"write" => false
		];

		$this->module = $module;

		##echo base64_encode(hash_hmac('sha256', $this->info['uuid'], $key, true));

		if($module) {
			if(
				isset($this->permissions[$module]) AND
				$this->permissions[$module]['token'] === base64_encode(hash_hmac('sha256', $this->info['uuid'], $key, true))
			) {
				$this->permission['read'] = $this->permissions[$module]['read'] == 1 ? true : false;
				$this->permission['write'] = $this->permissions[$module]['write'] == 1 ? true : false;
			} else {
				$this->permission['read'] = 0;
				$this->permission['write'] = 0;
			}
		}

		return $this->permission;
	}

	public function Update($array = []):bool {
		if($this->auth() AND is_array($array)) {
			$update = [];
			$data = [];
			$keys = array_keys($array);

			for($i=0;$i<sizeof($array);$i++) {
				$array[$keys[$i]] = (is_array($array[$keys[$i]]))? $this->core->jsonencode($array[$keys[$i]]):$array[$keys[$i]];

				$update[] = "{$keys[$i]} = ?";
				$data[] = $array[$keys[$i]];
			}

			$update = implode(',', $update);

			$sql = "UPDATE {$this->core->settings['dbprefix']}users SET
				{$update}
				WHERE
				uuid = '{$this->info['uuid']}'";

			$update = $this->core->Query($sql, $data);

			return $update;
		}

		return false;
	}

	public function queries() {
		$this->queries['active_count'] = $this->core->QuerySingleValue("SELECT COUNT(*) FROM app_items WHERE status = 1 AND options->>'uuid' = '{$this->info['uuid']}'");
		$this->queries['passive_count'] = $this->core->QuerySingleValue("SELECT COUNT(*) FROM app_items WHERE status > 1 AND options->>'uuid' = '{$this->info['uuid']}'");

		return $this->queries;
	}

	public function deleteAdminSession() {
		$_SESSION['isAdmin'] = '';
	}

	protected function getToken($uuid = '') {
		$uuid = (empty($uuid))?$this->info['uuid']:$uuid;

		return base64_encode(hash_hmac('sha256', $uuid . date('Ymdh'), SALT, true));
	}

	public function isAdmin() {
		if(
				isset($_SESSION['isAdmin']) AND !empty($_SESSION['isAdmin']) AND
				isset($_SESSION['adminToken']) AND !empty($_SESSION['adminToken']) AND
				$_SESSION['adminToken'] == $this->getToken($_SESSION['isAdmin'])
			)
			return true;

		return false;
	}

	public function saveAdmin() {
		$_SESSION['isAdmin'] = $this->info['uuid'];
		$_SESSION['adminToken'] = $this->getToken();

		if(
				$_SESSION['isAdmin'] == $this->info['uuid'] AND
				$_SESSION['adminToken'] == $this->getToken()
			)
			return true;

		return false;
	}

	public function invoices() {
		$this->info['invoices']['count_unpiad'] = '0';
	}

	public function companies() {
		$sql = "SELECT COUNT(e.*) FROM excelfirmalar e WHERE firmaid = {$this->info['properties']['fid']} AND silindi = false";
		$this->info['companies']['active_count'] = $this->core->QuerySingleValue($sql);
		$sql = "SELECT COUNT(e.*) FROM excelfirmalar e WHERE firmaid = {$this->info['properties']['fid']} AND silindi = true";
		$this->info['companies']['passive_count'] = $this->core->QuerySingleValue($sql);
	}

  public function auth(): bool {
		$sql = "SELECT * FROM {$this->core->settings['dbprefix']}sessions WHERE
			key = '{$this->core->identityCookieName}' AND
			value = '{$this->core->identityCookieValue}' AND
			ipaddress = '".$this->core->IP()['IPLONG']."'
		";

		$session = $this->core->QuerySingleRowArray($sql);

		if(isset($session['uuid']) AND !empty($session['uuid'])) {
			$sql = "SELECT * FROM {$this->core->settings['dbprefix']}users WHERE
				uuid = '{$session['uuid']}'
			";

			$this->info = $this->core->QuerySingleRowArray($sql);

			if(!$this->info)
				return false;

			#$this->info['group'] = array('id' => 1, 'name' => 'Yönetici Grubu');
			$this->info['properties'] = $this->core->jsondecode($this->info['properties']);
			$this->permissions = $this->core->jsondecode($this->info['permissions']);

			if(isset($this->info['sessions']) AND !empty($this->info['sessions']))
				$this->info['sessions'] = array_reverse($this->core->jsondecode($this->info['sessions']));
			//$this->info['group'] = $this->db->where("id", $this->info['options']['group'])->getOne('user_groups');
			#$this->core->user = $this->info;

			$this->auth = true;
			$this->token();
			$this->companies();
			$this->invoices();
			//$this->groups();
			//$this->permissions();
			if($this->info['properties']['type'] == 1 AND $this->info['properties']['verify_accountant'] != 1) {
				$this->info['accountant_request'] = $this->core->QuerySingleValue("SELECT COUNT(*) FROM {$this->core->settings['dbprefix']}requests
					WHERE
						json->>'type' = 'accountantVerify' AND
						status = 1 AND
						uuid = '{$this->info['uuid']}'
				");
			}

			$this->core->view->append('user', $this->info);
		} else {
			$this->logout();
			$this->auth = false;
		}

		return $this->auth;
  }

	public function clearTokens() {
		return $this->core->Query("DELETE FROM {$this->core->settings['dbprefix']}tokens WHERE expiry < NOW()");
	}

	public function token() {
		$this->clearTokens();

		if(!isset($this->info['uuid']) AND empty($this->info['uuid']))
			return;

		$sql = "SELECT id,token FROM {$this->core->settings['dbprefix']}tokens
			WHERE expiry > NOW() AND uuid = '{$this->info['uuid']}'";

		$tokenRow = $this->core->QuerySingleRowArray($sql);

		if(empty($tokenRow['id'])) {
			$token = sha1(mt_rand(1, 90000) . 'SALT');

			$sql = "INSERT INTO {$this->core->settings['dbprefix']}tokens
				(token,expiry,uuid) VALUES
				('{$token}', NOW() + INTERVAL '1 HOUR', '{$this->info['uuid']}')
			";

			$c = $this->core->Query($sql);

			$sql = "SELECT id,token FROM {$this->core->settings['dbprefix']}tokens
				WHERE expiry > NOW() AND uuid = '{$this->info['uuid']}'";

			$tokenRow = $this->core->QuerySingleRowArray($sql);
		} else {
			$this->core->Query("UPDATE {$this->core->settings['dbprefix']}tokens SET expiry = NOW() + INTERVAL '1 HOUR'
				WHERE expiry > NOW() AND uuid = '{$this->info['uuid']}'");
		}

		$this->info['token'] = $tokenRow['token'];
		$this->info['tokenId'] = $tokenRow['id'];
		$this->core->cookie->setCookie($this->core->identityCookieNameToken, $this->info['token'], ['expires' => strtotime('+1 Hour')]);
		/*
		$properties =
		$this->core->Query("UPDATE {$this->core->settings['dbprefix']}users
		SET properties = properties || '{$properties}'::jsonb
		WHERE uuid = '{$user['uuid']}'
		");
		*/
	}

	public function clearCookies() {
		if (isset($_SERVER['HTTP_COOKIE'])) {
			$cookies = explode(';', $_SERVER['HTTP_COOKIE']);
			//print_r($cookies);
			foreach($cookies as $cookie) {
				$parts = explode('=', $cookie);
				$name = trim($parts[0]);

				if(
					$parts[1] != 'deleted' AND
					$name != 'SLSESS'
				) {
					$this->core->cookie->setCookie($this->core->identityCookieName, 'deleted', ['expires' => time() - 1000]);
				}
			}
		}
	}

	public function logout() {
		#$this->core->user->deleteAdminSession();
		//$this->clearCookies();
		/*
		if(!isset($this->info['uuid']))
			return false;
			*/

		$this->core->Query("DELETE FROM {$this->core->settings['dbprefix']}sessions WHERE
			key = '{$this->core->identityCookieName}' AND
			value = '{$this->core->identityCookieValue}' AND
			ipaddress = '".$this->core->IP()['IPLONG']."'
		");
		/*
		$this->core->Query("DELETE FROM {$this->core->settings['dbprefix']}sessions WHERE
			key = '{$this->core->identityCookieName}' AND
			value = '{$this->core->identityCookieValue}' AND
			ipaddress = '".$this->core->IP()['IPLONG']."' AND
			uuid = '{$this->info['uuid']}'
		");
		*/

		if($result) {
			return true;
		} else {
			return false;
		}
	}

	public function undelete($params) {
		if(empty($params['uuid']))
			return ['status' => 2, 'msg' => 'No UUID'];

		$uuid = $this->core->QuerySingleValue("SELECT uuid FROM {$this->core->settings['dbprefix']}users WHERE uuid = ?", [$params['uuid']]);

		if(!$uuid)
			return ['status' => 2, 'msg' => 'Kullanıcı bulunamadı.'];

		$queries = [];
		$user = $this->core->QuerySingleRowArray("SELECT status,uuid,properties->>'fid' AS fid FROM {$this->core->settings['dbprefix']}users WHERE uuid = ?", [$params['uuid']]);

		if($user['status'] == 1)
			return ['status' => 2, 'msg' => 'Kullanıcı aktif gözükmektdir, lütfen işlemi kontrol ediniz.'];

		$companies = $this->core->QueryArray("SELECT DISTINCT vergino FROM excelfirmalar WHERE firmaid = '{$user['fid']}'");
		$companies = array_map(function($e){
			return $e['vergino'];
		}, $companies);

		if($params['alsoUnBlock'] == 'true') {
			for($i=0;$i<sizeof($companies);$i++) {
				$queries[] = ["DELETE FROM {$this->core->settings['dbprefix']}blocklist WHERE options->>'vergino' = ?", [$companies[$i]]];
			}
		}

		$queries[] = ["UPDATE {$this->core->settings['dbprefix']}users SET status = 1 WHERE uuid = ?", [$params['uuid']]];
		$queries[] = ["UPDATE excelfirmalar SET silindi = false WHERE firmaid = ?", [$user['fid']]];

		$queries[] = ["DELETE FROM {$this->core->settings['dbprefix']}blocklist WHERE options->>'uuid' = ?", [$params['uuid']]];

		$status = $this->core->queryRollback($queries);

		if($status)
			return ['status' => 0, 'msg' => ''];

		return ['status' => 2, 'msg' => 'Bilinmeyen durum oluştu, lütfen işlemi kontrol ediniz.'];
	}

	public function delete($params) {
		if(empty($params['uuid']))
			return ['status' => 2, 'msg' => 'No UUID'];

		$uuid = $this->core->QuerySingleValue("SELECT uuid FROM {$this->core->settings['dbprefix']}users WHERE uuid = ?", [$params['uuid']]);

		if(!$uuid)
			return ['status' => 2, 'msg' => 'Kullanıcı bulunamadı.'];

		$queries = [];
		$user = $this->core->QuerySingleRowArray("SELECT status,uuid,properties->>'fid' AS fid FROM {$this->core->settings['dbprefix']}users WHERE uuid = ?", [$params['uuid']]);

		if($user['status'] != 1)
			return ['status' => 2, 'msg' => 'Kullanıcı engellenmiş veya kapalı durumdadır.'];

		$companies = $this->core->QueryArray("SELECT DISTINCT vergino FROM excelfirmalar WHERE firmaid = '{$user['fid']}'");
		$companies = array_map(function($e){
			return $e['vergino'];
		}, $companies);

		if($params['alsoBlock'] == 'true') {
			for($i=0;$i<sizeof($companies);$i++) {
				$json = $this->core->jsonencode(array('uuid' => $params['uuid'], 'desc' => 'Kullanıcıya bağlı tüm vergi numaraları engellenmiştir.', 'vergino' => (Int) $companies[$i]));
				$queries[] = ["INSERT INTO {$this->core->settings['dbprefix']}blocklist (type,options) VALUES (?, ?)",[1, $json]];
			}
		}

		$queries[] = ["UPDATE {$this->core->settings['dbprefix']}users SET status = 3 WHERE uuid = ?", [$params['uuid']]];
		$queries[] = ["UPDATE excelfirmalar SET silindi = true WHERE firmaid = ?", [$user['fid']]];

		$json = $this->core->jsonencode(
			array(
				'desc' => 'Bu kullanıcı ve kullanıcıya bağlı e-posta, telefon bilgileri yönetici tarafından engellenmiştir.',
				'admin_desc' => $params['adminDesc'],
				'uuid' => $params['uuid'],
				'vergino' => implode(',', $companies)
			)
		);
		$queries[] = ["INSERT INTO {$this->core->settings['dbprefix']}blocklist (type,options) VALUES (?, ?)", [2, $json]];

		$status = $this->core->queryRollback($queries);

		if($status)
			return ['status' => 0, 'msg' => ''];

		return ['status' => 2, 'msg' => 'Bilinmeyen durum oluştu, lütfen işlemi kontrol ediniz.'];
	}

	public function login($forceLogin = false, $uuid = '') {
		$this->logout();

		if($forceLogin) {
			$this->core->post['email'] = 'FORCE';
			$this->core->post['password'] = 'FORCE';
		}

		if(
			isset($this->core->post['email']) AND
			isset($this->core->post['password'])
		) {

			//$val = new Validation();
	    //$val->name('email')->value($this->core->post['email'])->required()->pattern('email')->max(40);
			//!$val->isSuccess()

			if(!$forceLogin AND !Validation::is_email($this->core->post['email'])) {
				return array('alert' => 'warning', 'text' => 'E-posta adresinizi doğru yazdığınızdan emin olunuz.', 'status' => 2);
			}

			$where = [];
			$data = [];

			if($forceLogin) {
				$where[] = "uuid = '{$uuid}'";
				$data[] = $uuid;
			} else {
				$where[] = "properties->>'email' = '{$this->core->post['email']}'";
				$data[] = $this->core->post['email'];
			}

			/*
			if(!$forceLogin)
				$where[] = "status = 1";
			*/

			$where = implode(' AND ', $where);

			$sql = "SELECT * FROM {$this->core->settings['dbprefix']}users
				WHERE
				{$where}
					";

			$user = $this->core->QuerySingleRowArray($sql);

			if(!$forceLogin) {
				$blockList = $this->core->QuerySingleRowArray("SELECT options->>'admin_desc' AS msg,published FROM {$this->core->settings['dbprefix']}blocklist WHERE options->>'uuid' = '{$user['uuid']}'");

				if($blockList) {
					return ['status' => 3, 'msg' => $blockList['msg'], 'time' => $blockList['published']];
				}
			}

			if($user['status'] != 1 AND !$forceLogin)
				return ['status' => 2, 'Hatalı kullanıcı'];

			$user['properties'] = $this->core->jsondecode($user['properties']);
			//$user['firstLogin'] = (empty($user['sessions']))?true:false;

			if(
				$forceLogin OR
				(
					!empty($this->core->post['password']) AND
					isset($user['properties']['password']) AND
					!empty($user['properties']['password']) AND
					$this->passwordVerify($this->core->post['password'], $user['properties']['password'])
				)
			) {
				$this->core->identityCookie(true);

				$sql = "INSERT INTO {$this->core->settings['dbprefix']}sessions
				(key,value,expiry,domain,uuid,ipaddress)
					VALUES (
						'{$this->core->identityCookieName}',
						'{$this->core->identityCookieValue}',
						'".date('Y-m-d h:i:s', $this->core->settings['cookie']['lifetime'])."',
						'{$this->core->settings['domain']['host']}',
						'{$user['uuid']}',
						'".$this->core->IP()['IPLONG']."'
					)";
				// remove any other session
				/*
				if($user['properties']['multiLogin'] != 1)
					$this->core->Query("DELETE FROM {$this->core->settings['dbprefix']}sessions WHERE uuid = '{$user['uuid']}'");
					*/

				$insert = $this->core->Query($sql);

				if(!$insert) {
					$session = json_encode(array('date' => date('Y-m-d H:i:s'), 'ip' => $this->core->IP()['IPADDR'], 'status' => 1));

					$this->core->Query("UPDATE {$this->core->settings['dbprefix']}users
					SET sessions = sessions || '{$session}'::jsonb
					WHERE uuid = '{$user['uuid']}'
					");

					return ['status' => 2, 'msg' => 'Teknik Hata Oluştu.'];
				}

				$session = json_encode(array(time() => array('date' => date('Y-m-d H:i:s'), 'ip' => $this->core->IP()['IPADDR'], 'status' => 0)));

				$sql = "UPDATE {$this->core->settings['dbprefix']}users
				SET
				sessions = sessions || '{$session}'::jsonb
				WHERE uuid = '{$user['uuid']}'
				";

				$this->core->Query($sql);

				return ['status' => 0, 'msg' => ''];
			} else {
				$session = json_encode(array(time() => array('date' => date('Y-m-d H:i:s'), 'ip' => $this->core->IP()['IPADDR'], 'status' => 0)));

				$this->core->Query("UPDATE {$this->core->settings['dbprefix']}users
				SET
				sessions = sessions || '{$session}'::jsonb
				WHERE uuid = '{$user['uuid']}'
				");
				//sessions[] = array_append(sessions[],'{$session}')
				//sessions = sessions || '{$session}'::jsonb
			}
		}

		return ['status' => 2, 'msg' => 'Bilinmeyen durum oluştu.'];
	}

	public function generatepass($password) {
		return password_hash($password, PASSWORD_DEFAULT, ['cost' => COST]);
	}

	static function passwordVerify($password, $hash) {
		return password_verify($password, $hash);
	}
}
