<?php

namespace Staffbase\plugins\sdk;

use SessionHandlerInterface;
use Staffbase\plugins\sdk\Helper\URLHelper;
use Staffbase\plugins\sdk\Exceptions\SSOAuthenticationException;
use Staffbase\plugins\sdk\Exceptions\SSOException;
use Staffbase\plugins\sdk\RemoteCall\DeleteInstanceTrait;
use Staffbase\plugins\sdk\RemoteCall\RemoteCallInterface;
use Staffbase\plugins\sdk\SessionHandling\SessionTokenDataTrait;
use Staffbase\plugins\sdk\SSOData\HeaderSSOData;

class HeaderSession
{
    use HeaderSSOData, SessionTokenDataTrait, DeleteInstanceTrait;

    /**
     * Session cookies from staffbase are prefixed with sid_ this should
     * exclude every other possible cookie
     */
    private const COOKIE_PREFIX = "sid_";

    /**
     * The path for the plugin in the experience studio has the pattern
     * `pluginId/instanceId/studio`
     */
    private const EDITOR_STUDIO_PATH = "studio";

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
        $sso = ($jwt = $this->validateParams($pluginId)) ? new HeaderToken($appSecret, $jwt, $leeway) : null;

        // delete the instance if the special sub is in the token data
        // exits the request
        if ($sso && $remoteCallHandler && $sso->isDeleteInstanceCall()) {
            $this->deleteInstance($this->pluginInstanceId, $remoteCallHandler);
        }

        $this->storeSessionInfoFromCookie();

        $this->openSession($this->session_name, $this->sessionId);

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
     * @throws SSOAuthenticationException
     */
    private function validateParams(string $pluginId): ?string
    {
        $jwt = $this->getHeaderAuthorizationToken();
        $this->pluginInstanceId = URLHelper::getParam($pluginId);

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

    /**
     * Strips the string "Bearer " from the Authorization header value and returns the JWT token
     * @param string $value
     * @return false|string
     */
    private function getToken(string $value)
    {
        return substr($value, 7);
    }

    /**
     * Checks if the request is made as an editor
     *
     * @return bool
     */
    private function isAdminView(): bool
    {
        return $this->isEditor() && URLHelper::getParam(self::EDITOR_STUDIO_PATH);
    }
}
