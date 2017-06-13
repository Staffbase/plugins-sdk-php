<?php
/**
 * SSO Session implementation, based on this doc:
 * https://developers.staffbase.com/api/plugin-sso/
 *
 * PHP version 5.5.9
 *
 * @category  Authentication
 * @copyright 2017 Staffbase, GmbH.
 * @author    Vitaliy Ivanov
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 * @link      https://github.com/staffbase/plugins-sdk-php
 */

namespace Staffbase\plugins\sdk;

use Exception;
use SessionHandlerInterface;
use Staffbase\plugins\sdk\SSOData;
use Staffbase\plugins\sdk\SSOToken;

/**
 * A container which decrypts and stores the SSO data in a session for further requests.
 */
class PluginSession extends SSOData
{
	const QUERY_PARAM_JWT = 'jwt';
	const QUERY_PARAM_PID = 'pid';

	const KEY_SSO  = 'sso';
	const KEY_DATA = 'data';

	/** 
	 * @var $pluginInstanceId the id of the currently used instance.
	 */
	private $pluginInstanceId  = null;

	/**
	 * Constructor
	 * 
	 * @param $pluginId the unique name of the plugin
	 * @param $appSecret application public key
	 * @param $sessionHandler optional custom session handler
	 * 
	 * @throws Exception
	 */
	public function __construct($pluginId, $appSecret, SessionHandlerInterface $sessionHandler = null)
	{
		if (!$pluginId)
			throw new Exception('Empty plugin ID.');

		if (!$appSecret)
			throw new Exception('Empty app secret.');

		if ($sessionHandler)
			session_set_save_handler($sessionHandler, true);

		$this->openSession($pluginId);

		$pid = isset($_GET[self::QUERY_PARAM_PID]) ? $_GET[self::QUERY_PARAM_PID] : null;
		$jwt = isset($_GET[self::QUERY_PARAM_JWT]) ? $_GET[self::QUERY_PARAM_JWT] : null;

		// lets hint to bad class usage, as these cases should never happen.

		if($pid && $jwt) {
			throw new Exception('Tried to initialize the session with both PID and JWT provided.');
		}

		if (!$pid && !$jwt) {
			throw new Exception('Missing PID or JWT query parameter in Reuest.');
		}

		$this->pluginInstanceId = $pid;

		// we update the SSO info every time we get a token
		if ($jwt) {

			// convert secret to PEM if its a plain base64 string
			if(strpos(trim($appSecret),'-----') !== 0 && strpos(trim($appSecret), 'file://') !==0 )
				$appSecret = self::base64ToPEMPublicKey($appSecret);

			// decrypt the token
			$sso = new SSOToken($appSecret, $jwt);
			$ssoData = $sso->getData();

			// update data

			$this->pluginInstanceId = $sso->getInstanceId();

			$_SESSION[$this->pluginInstanceId][self::KEY_SSO] = $ssoData;
		}

		// requests with spoofed PID are not allowed
		if (!isset($_SESSION[$this->pluginInstanceId][self::KEY_SSO]) 
		|| empty($_SESSION[$this->pluginInstanceId][self::KEY_SSO]))
			throw new Exception('Tried to access an instance without previous authentication.');
	}

	/**
	 * Destructor
	 */
	public function __destruct() {
		$this->closeSession();
	}

	/**
	 * Open a session.
	 *
	 * @param string $name of the session
	 */
	protected function openSession($name) {
		session_name($name);
		session_start();
	}	

	/**
	 * Close a session.
	 */
	protected function closeSession() {
		session_write_close();
	}

	/**
	 * Translate a base64 string to PEM encoded public key.
	 *
	 * @param string $data base64 encoded key
	 *
	 * @return string PEM encoded key
	 */
	public static function base64ToPEMPublicKey($data)
	{

		$data = strtr($data, array(
			"\r" => "",
			"\n" => ""
		));

		return
			"-----BEGIN PUBLIC KEY-----\n".
			chunk_split($data, 64, "\n").
			"-----END PUBLIC KEY-----\n";
	}

	/**
	 * Test if a claim is set.
	 *
	 * @param string $claim name.
	 *
	 * @return boolean
	 */
	protected function hasClaim($claim) {
		return isset($_SESSION[$this->pluginInstanceId][self::KEY_SSO][$claim]);
	}

	/**
	 * Get a claim without checking for existence.
	 *
	 * @param string $claim name.
	 *
	 * @return mixed
	 */
	protected function getClaim($claim) {
		return $_SESSION[$this->pluginInstanceId][self::KEY_SSO][$claim];
	}

	/**
	 * Get an array of all available claims.
	 *
	 * @return array
	 */
	protected function getAllClaims() {
		return $_SESSION[$this->pluginInstanceId][self::KEY_SSO];
	}

	/**
	 * Get a previously set session variable.
	 *
	 * @param mixed $key
	 *
	 * @return mixed|null
	 */
	public function getSessionVar($key) {
		if(isset($_SESSION[$this->pluginInstanceId][self::KEY_DATA][$key]))
			return $_SESSION[$this->pluginInstanceId][self::KEY_DATA][$key];

		return null;
	}

	/**
	 * Get an array of all previously set session variables.
	 *
	 * @return array
	 */
	public function getSessionData() {
		if(isset($_SESSION[$this->pluginInstanceId][self::KEY_DATA]))
			return $_SESSION[$this->pluginInstanceId][self::KEY_DATA];

		return [];
	}

	/**
	 * Set a session variable.
	 *
	 * @param mixed $key
	 * @param mixed $val
	 */
	public function setSessionVar($key, $val) {
		$_SESSION[$this->pluginInstanceId][self::KEY_DATA][$key] = $val;
	}
}
