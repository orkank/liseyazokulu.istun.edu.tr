<?php
namespace master {
	/**
	 * @author saurabh goyal
	 */

	class CookieManager {
	    protected  $path;
	    protected  $domain;
	    protected  $expire;
	    protected  $secure;

	    /**
	     * __construct
	     * @param mixed $path,$domain,$expire
	     * @param boolean $secure,$httpOnly
	     * @param mixed $objResumeUpdateManager
	     * @return void
	     */

	    public function __construct(&$settings) {
	        $this->path = $settings['cookie']['path'];
	        $this->domain = $settings['domain']['host'];
	        $this->expire = $settings['cookie']['lifetime'];
	        $this->secure = $settings['cookie']['secure'];
					$this->samesite = $settings['cookie']['samesite'];
	        $this->httpOnly = $settings['cookie']['httpOnly'];
	    }

			static public function getCookie($name) {
				return isset($_COOKIE[$name]) ? $_COOKIE[$name] : false;
	    }

	    public function setCookie($name,$value, $options = []) {
				$cookie_options = array (
          'expires' => $this->expire,
          'path' => $this->path,
          'domain' => $this->domain, // leading dot for compatibility or use subdomain
          'secure' => $this->secure,     // or false
          'httponly' => $this->httpOnly,    // or false
          'samesite' => $this->samesite // None || Lax  || Strict
        );

				return setcookie($name, $value, array_merge($cookie_options,$options));
	    }
	      /*
	     * Set setCookie
	     * @param string $name
	     * @param string $value
	     * return boolean
	     */
	    public function setSerializeCookie($name,$value) {
	        try {
	            $this->setCookie($name, serialize($value));
	            return true;
	        } catch (Exception $e) {
	            return false;
	        }
	    }

	    public function getUnSerializeCookie($name) {
	        return (isset($_COOKIE[$name]) && $_COOKIE[$name]!='')?unserialize($_COOKIE[$name]):false;
	    }

	     /*
	     * Set setCookieArray
	     * @param string $name
	     * @param array $arrayValue
	     * return boolean
	     */

	    public function setCookieArray($name,$arrayValue) {
	        $cookieArray = $this->getUnSerializeCookie($name);
	        if($cookieArray) {
	             $arrayValue = $arrayValue+$cookieArray;
	        }
	        $this->setSerializeCookie($name,$arrayValue);
	        return true;
	    }

	    /*
	     * Set unsetCookieArray
	     * @param string $name
	     * @param string $value key of array
	     * return boolean
	     */
	    public function unsetCookieArrayByKey($name,$key) {
	         $cookieArray = $this->getUnSerializeCookie($name);
	         if($cookieArray && (array_key_exists($key,$cookieArray) !== false)) {
	             unset($cookieArray[$key]);
	             $this->setSerializeCookie($name,$cookieArray);
	         }
	         return true;
	    }

	    public function getCookieValueByKey($name,$key) {
	        $cookieArray = $this->getUnSerializeCookie($name);
	        if($cookieArray) {
	           return isset($cookieArray[$key])?$cookieArray[$key]:false;
	        }
	        return false;
	    }

	}
}
