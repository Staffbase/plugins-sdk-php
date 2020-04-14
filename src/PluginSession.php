<?php
/**
 * SSO Session implementation, based on this doc:
 * https://developers.staffbase.com/api/plugin-sso/
 *
 * PHP version 5.5.9
 *
 * @category  Authentication
 * @copyright 2017-2019 Staffbase, GmbH.
 * @author    Vitaliy Ivanov
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 * @link      https://github.com/staffbase/plugins-sdk-php
 */

namespace Staffbase\plugins\sdk;

use SessionHandlerInterface;
use Staffbase\plugins\sdk\Exceptions\SSOException;
use Staffbase\plugins\sdk\Exceptions\SSOAuthenticationException;
use Staffbase\plugins\sdk\RemoteCall\RemoteCallInterface;
use Staffbase\plugins\sdk\RemoteCall\DeleteInstanceCallHandlerInterface;

/**
 * A container which decrypts and stores the SSO data in a session for further requests.
 */
class PluginSession extends SSOData
{
	const QUERY_PARAM_JWT = 'jwt';
	const QUERY_PARAM_PID = 'pid';
	const QUERY_PARAM_USERVIEW = 'userView';

	const KEY_SSO  = 'sso';
	const KEY_DATA = 'data';

	/**
	 * @var String $pluginInstanceId the id of the currently used instance.
	 */
	private $pluginInstanceId  = null;

	/**
	 * @var boolean $userView flag for userView mode.
	 */
	private $userView = true;

	/**
	 * Constructor
	 *
	 * @param string $pluginId the unique name of the plugin
	 * @param string $appSecret application public key
	 * @param SessionHandlerInterface $sessionHandler optional custom session handler
	 * @param int $leeway in seconds to compensate clock skew
	 * @param RemoteCallInterface $remoteCallHandler a class handling remote calls
	 *
	 * @throws SSOAuthenticationException | SSOException
	 */
	public function __construct($pluginId, $appSecret, SessionHandlerInterface $sessionHandler = null, $leeway = 0, RemoteCallInterface $remoteCallHandler = null) {

		if (!$pluginId)
			throw new SSOException('Empty plugin ID.');

		if (!$appSecret)
			throw new SSOException('Empty app secret.');

		if ($sessionHandler)
			session_set_save_handler($sessionHandler, true);

		$this->openSession($pluginId);

		$pid = isset($_GET[self::QUERY_PARAM_PID]) ? $_GET[self::QUERY_PARAM_PID] : null;
		$jwt = isset($_GET[self::QUERY_PARAM_JWT]) ? $_GET[self::QUERY_PARAM_JWT] : null;

		// lets hint to bad class usage, as these cases should never happen.

		if($pid && $jwt) {
			throw new SSOAuthenticationException('Tried to initialize the session with both PID and JWT provided.');
		}

		if (!$pid && !$jwt) {
			throw new SSOAuthenticationException('Missing PID or JWT query parameter in Request.');
		}

		$this->pluginInstanceId = $pid;

		// we update the SSO info every time we get a token
		if ($jwt) {

			// decrypt the token
			$sso = new SSOToken($appSecret, $jwt, $leeway);
			$ssoData = $sso->getData();

			// dispatch remote calls from Staffbase
			if ($sso->isDeleteInstanceCall() && $remoteCallHandler) {

				// we will accept unhandled calls with a warning
				$result = true;

				$instanceId = $sso->getInstanceId();

				if ($remoteCallHandler instanceOf DeleteInstanceCallHandlerInterface) {
					$result = $remoteCallHandler->deleteInstance($instanceId);
				} else {
					error_log("Warning: An instance deletion call for instance $instanceId was not handled.");
				}

				// finish the remote call
				if($result)
					$remoteCallHandler->exitSuccess();
				else
					$remoteCallHandler->exitFailure();

				$this->exitRemoteCall();
			}

			// update data
			$this->pluginInstanceId = $sso->getInstanceId();
			$_SESSION[$this->pluginInstanceId][self::KEY_SSO] = $ssoData;
		}

		// requests with spoofed PID are not allowed
		if (!isset($_SESSION[$this->pluginInstanceId][self::KEY_SSO])
		  || empty($_SESSION[$this->pluginInstanceId][self::KEY_SSO]))
			throw new SSOAuthenticationException('Tried to access an instance without previous authentication.');

		// decide if we are in user view or not
		if($this->isEditor() && (!isset($_GET[self::QUERY_PARAM_USERVIEW]) || $_GET[self::QUERY_PARAM_USERVIEW] !== 'true'))
			$this->userView = false;
	}

	/**
	 * Destructor
	 */
	public function __destruct() {

		$this->closeSession();
	}

	/**
	 * Exit the script
	 *
	 * if a remote call was not handled by the user we die hard here
	 */
	protected function exitRemoteCall() {
		error_log("Warning: The exit procedure for a remote call was not properly handled.");
		exit;
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
	 * (DEPRECATED) Translate a base64 string to PEM encoded public key.
	 *
	 * @param string $data base64 encoded key
	 *
	 * @return string PEM encoded key
	 */
	public static function base64ToPEMPublicKey($data) {

		error_log("Warning: PluginSession::base64ToPEMPublicKey() is deprecated. Please switch over to  SSOToken::base64ToPEMPublicKey().");

		return SSOToken::base64ToPEMPublicKey($data);
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

	/**
	 * Test if userView is enabled.
	 *
	 * @return bool
	 */
	public function isUserView() {

		return $this->userView;
	}

}
