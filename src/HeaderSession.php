<?php

namespace Staffbase\plugins\sdk;

use RuntimeException;
use SessionHandlerInterface;
use Staffbase\plugins\sdk\Exceptions\SSOAuthenticationException;
use Staffbase\plugins\sdk\Exceptions\SSOException;
use Staffbase\plugins\sdk\RemoteCall\DeleteInstanceCallHandlerInterface;
use Staffbase\plugins\sdk\RemoteCall\RemoteCallInterface;
use Staffbase\plugins\sdk\SessionHandling\SessionTokenDataTrait;
use Staffbase\plugins\sdk\SSOData\HeaderSSOData;

class HeaderSession
{
    use HeaderSSOData, SessionTokenDataTrait;

    /**
     * Session cookies from staffbase are prefixed with sid_ this should
     * exclude every other possible cookie
     */
    private const COOKIE_PREFIX = "sid_";

    /**
     * @var String|null $pluginInstanceId the id of the currently used instance.
     */
    private ?string $pluginInstanceId = null;

    /**
     * @var string|null $session_name the name of the session
     */
    private ?string $session_name;

    /**
     * @var String|null $sessionId the id of the current session.
     */
    private ?string $sessionId = null;

    /**
     * @var boolean $userView flag for userView mode.
     */
    private bool $userView;

    /**
     * @var HeaderToken|null token data from the parsed jwt
     */
    private ?HeaderToken $sso = null;


    /**
     * @throws SSOAuthenticationException
     * @throws SSOException
     */
    public function __construct(string $pluginId, string $appSecret, ?SessionHandlerInterface $sessionHandler = null, int $leeway = 0, ?RemoteCallInterface $remoteCallHandler = null)
    {
        if (!$pluginId) {
            throw new SSOException('Empty plugin ID.');
        }

        if ($sessionHandler) {
            session_set_save_handler($sessionHandler, true);
        }

        // we update the SSO info every time we get a token
        if ($jwt = $this->validateParams($pluginId)) {
            $this->sso = new HeaderToken($appSecret, $jwt, $leeway);
        }

        // delete the instance if the special sub is in the token data
        if ($this->sso && $remoteCallHandler) {
            $this->deleteInstance($remoteCallHandler);
        }

        $this->storeSessionInfoFromCookie();

        $this->openSession($this->session_name, $this->sessionId);

        if ($this->sso !== null) {
            $this->setClaims($this->sso->getData());
        }

        // decide if we are in user view or not
        $this->userView = !$this->isAdminView();

        // requests with spoofed PID are not allowed
        if (empty($this->getAllClaims())) {
            throw new SSOAuthenticationException('Tried to access an instance without previous authentication.');
        }
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
     * @throws SSOAuthenticationException
     */
    private function validateParams(string $pluginId): ?string
    {
        $jwt = $this->getHeaderAuthorizationToken();
        $this->pluginInstanceId = getParam($pluginId);

        return $jwt;
    }

    /**
     * Tries the apache headers and the header directly to get the authorization token from.
     *
     * @returns string|null the jwt token
     * @throws SSOAuthenticationException if no header value is found
     */
    private function getHeaderAuthorizationToken(): ?string
    {
        if (($headers = apache_request_headers()) && isset($headers['Authorization'])) {
            return $this->getToken($headers['Authorization']);
        }

        if (isset($_SERVER["HTTP_AUTHORIZATION"])) {
            return $this->getToken($_SERVER["HTTP_AUTHORIZATION"]);
        }

        throw new SSOAuthenticationException("No Authorization field set.");
    }

    /**
     * Searches the cookie with the "sid" prefix and gets name and session id from it
     * @return void
     */
    private function storeSessionInfoFromCookie(): void
    {
        $cookies = array_filter(
            $_COOKIE,
            static fn($key) => stripos($key, self::COOKIE_PREFIX) === 0,
            ARRAY_FILTER_USE_KEY
        );

        $this->session_name = array_key_first($cookies);
        $this->sessionId = array_values($cookies)[0];
    }


    private function deleteInstance(RemoteCallInterface $remoteCallHandler): void
    {
        if (!$this->sso->isDeleteInstanceCall()) {
            return;
        }

        $instanceId = $this->pluginInstanceId;

        if ($remoteCallHandler instanceof DeleteInstanceCallHandlerInterface) {
            $result = $remoteCallHandler->deleteInstance($instanceId);
        } else {
            // we will accept unhandled calls with a warning
            $result = true;
            error_log("Warning: An instance deletion call for instance $instanceId was not handled.");
        }

        // finish the remote call
        if ($result) {
            $remoteCallHandler->exitSuccess();
        } else {
            $remoteCallHandler->exitFailure();
        }

        $this->exitRemoteCall();
    }

    /**
     * Exit the script
     *
     * if a remote call was not handled by the user we die hard here
     */
    protected function exitRemoteCall(): void
    {
        error_log("Warning: The exit procedure for a remote call was not properly handled.");
        exit;
    }

    /**
     * Strips the string "Bearer " from the Authorization header value and returns the JWT token
     * @param string $value
     * @return false|string
     */
    private function getToken(string $value)
    {
        return substr($value, 7);
    }

    private function isAdminView(): bool
    {
        return $this->isEditor() && (!isset($_GET[self::QUERY_PARAM_USERVIEW]) || $_GET[self::QUERY_PARAM_USERVIEW] !== 'true');
    }
}
