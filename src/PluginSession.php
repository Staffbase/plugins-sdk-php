<?php
/**
 * SSO Session implementation, based on this doc:
 * https://developers.staffbase.com/api/plugin-sso/
 *
 * PHP version 7.4
 *
 * @category  Authentication
 * @copyright 2017-2019 Staffbase, GmbH.
 * @author    Vitaliy Ivanov
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 * @link      https://github.com/staffbase/plugins-sdk-php
 */

namespace Staffbase\plugins\sdk;

use SessionHandlerInterface;
use Staffbase\plugins\sdk\AuthType\QueryParamToken;
use Staffbase\plugins\sdk\Exceptions\SSOAuthenticationException;
use Staffbase\plugins\sdk\Exceptions\SSOException;
use Staffbase\plugins\sdk\RemoteCall\DeleteInstanceTrait;
use Staffbase\plugins\sdk\RemoteCall\RemoteCallInterface;
use Staffbase\plugins\sdk\SessionHandling\SessionTokenDataTrait;
use Staffbase\plugins\sdk\SSOData\SSOData;

/**
 * A container which decrypts and stores the SSO data in a session for further requests.
 */
class PluginSession
{
    use SSOData, SessionTokenDataTrait, DeleteInstanceTrait;

    public const QUERY_PARAM_JWT = 'jwt';
    public const QUERY_PARAM_PID = 'pid';
    public const QUERY_PARAM_SID = 'sessionID';
    public const QUERY_PARAM_USERVIEW = 'userView';

    /**
     * @var String|null $pluginInstanceId the id of the currently used instance.
     */
    private ?string $pluginInstanceId = null;

    /**
     * @var String|null $sessionId the id of the current session.
     */
    private ?string $sessionId = null;

    /**
     * @var boolean $userView flag for userView mode.
     */
    private bool $userView;

    /**
     * Constructor
     *
     * @param string $pluginId the unique name of the plugin
     * @param string $appSecret application public key
     * @param SessionHandlerInterface|null $sessionHandler optional custom session handler
     * @param int $leeway in seconds to compensate clock skew
     * @param RemoteCallInterface|null $remoteCallHandler a class handling remote calls
     *
     * @throws SSOAuthenticationException
     * @throws SSOException
     */
    public function __construct(
        string $pluginId,
        string $appSecret,
        ?SessionHandlerInterface $sessionHandler = null,
        int $leeway = 0,
        ?RemoteCallInterface $remoteCallHandler = null
    ) {
        if (!$pluginId) {
            throw new SSOException('Empty plugin ID.');
        }

        if ($sessionHandler) {
            session_set_save_handler($sessionHandler, true);
        }

        // we update the SSO info every time we get a token
        $sso =($jwt = $this->validateParams()) ? $this->updateSSOInformation($jwt, $appSecret, $leeway) : null;

        // delete the instance if the special sub is in the token data
        // exits the request
        if ($sso && $remoteCallHandler && $sso->isDeleteInstanceCall()) {
            $this->deleteInstance($sso->getInstanceId(), $remoteCallHandler);
        }

        // starts the session
        $this->openSession($pluginId, $this->createCompatibleSessionId($this->sessionId));

        // sets all claims if the token is refreshed
        if ($sso !== null) {
            $this->setClaims($sso->getData());
        }

        // decide if we are in user view or not
        $this->userView = !$this->isAdminView();

        // requests with spoofed PID are not allowed
        if (empty($this->getAllClaims())) {
            throw new SSOAuthenticationException('Tried to access an instance without previous authentication.');
        }
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->closeSession();
    }

    /**
     * Test if userView is enabled.
     *
     * @return bool
     */
    public function isUserView(): bool
    {
        return $this->userView;
    }

    /**
     * Decrypts the token and stores it in the class properties
     *
     * @param string $jwt
     * @param string $appSecret
     * @param int $leeway
     *
     * @throws SSOAuthenticationException
     * @throws SSOException
     */
    private function updateSSOInformation(string $jwt, string $appSecret, int $leeway = 0): SSOToken
    {
        // decrypt the token
        $sso = new SSOToken($appSecret, $jwt, $leeway);

        $this->pluginInstanceId = $sso->getInstanceId();
        $this->sessionId = $sso->getSessionId() ?: $sso->getInstanceId();

        return $sso;
    }

    /**
     * Check the query params, handles conflict cases and sets the properties
     *
     * @throws SSOAuthenticationException
     */
    private function validateParams(): ?string
    {
        $pid = $_REQUEST[self::QUERY_PARAM_PID] ?? null;
        $jwt = $_REQUEST[self::QUERY_PARAM_JWT] ?? null;
        $sid = $_REQUEST[self::QUERY_PARAM_SID] ?? null;

        // lets hint to bad class usage, as these cases should never happen.
        if ($pid && $jwt) {
            throw new SSOAuthenticationException('Tried to initialize the session with both PID and JWT provided.');
        }

        if (!$pid && !$jwt) {
            throw new SSOAuthenticationException('Missing PID or JWT query parameter in Request.');
        }

        $this->pluginInstanceId = $pid;
        $this->sessionId = $sid ?: $pid;

        return $jwt;
    }

    private function isAdminView(): bool
    {
        return $this->isEditor() && (!isset($_GET[self::QUERY_PARAM_USERVIEW]) || $_GET[self::QUERY_PARAM_USERVIEW] !== 'true');
    }
}
