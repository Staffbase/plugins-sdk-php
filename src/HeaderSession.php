<?php
declare(strict_types=1);

/**
 * SSO data implementation to work with Session data received as
 * header attribute.
 *
 * PHP version 7.4
 *
 * @category  Authentication
 * @copyright 2017-2022 Staffbase, GmbH.
 * @author    Daniel Grosse
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 * @link      https://github.com/staffbase/plugins-sdk-php
 */

namespace Staffbase\plugins\sdk;

use InvalidArgumentException;
use Staffbase\plugins\sdk\Helper\URLHelper;
use Staffbase\plugins\sdk\Exceptions\SSOAuthenticationException;
use Staffbase\plugins\sdk\Exceptions\SSOException;
use Staffbase\plugins\sdk\RemoteCall\DeleteInstanceTrait;
use Staffbase\plugins\sdk\RemoteCall\RemoteCallInterface;
use Staffbase\plugins\sdk\SessionHandling\TokenDataTrait;
use Staffbase\plugins\sdk\SSOData\HeaderSSODataTrait;

/**
 * A container which decrypts and stores the SSO data in a session for further requests.
 */
class HeaderSession
{
    use HeaderSSODataTrait, TokenDataTrait, DeleteInstanceTrait;

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
        int $leeway = 0,
        ?RemoteCallInterface $remoteCallHandler = null
    ) {
        if (!$pluginId) {
            throw new SSOException('Empty plugin ID.');
        }

        // we update the SSO info every time we get a token
        $sso = new HeaderToken($appSecret, $this->validateParams($pluginId), $leeway);
        $this->setClaims($sso->getData());

        // delete the instance if the special sub is in the token data
        // exits the request
        if ($remoteCallHandler && $sso->isDeleteInstanceCall()) {
            $this->deleteInstance($this->pluginInstanceId, $remoteCallHandler);
        }

        // decide if we are in user view or not
        $this->userView = !$this->isAdminView();
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
    private function validateParams(string $pluginId): string
    {
        $jwt = $this->getHeaderAuthorizationToken();
        $this->pluginInstanceId = URLHelper::getParam($pluginId);

        if (!$this->isValidInstanceId($this->pluginInstanceId ?? "")) {
            throw new InvalidArgumentException("Instance ID is not valid: $this->pluginInstanceId");
        }

        return $jwt;
    }

    /**
     * Checks if the instance id is a valid object id
     * @param string $oid
     * @return bool
     */
    private function isValidInstanceId(string $oid): bool
    {
        return strlen($oid) === 24 && strspn($oid, '0123456789ABCDEFabcdef') === 24;
    }


    /**
     * Tries the apache headers and the header directly to get the authorization token from.
     *
     * @returns string the jwt token
     * @throws SSOAuthenticationException if no header value is found
     */
    private function getHeaderAuthorizationToken(): string
    {
        if (($headers = $this->getHeaders()) && isset($headers['Authorization'])) {
            return $this->getToken($headers['Authorization']);
        }

        throw new SSOAuthenticationException("No Authorization field set.");
    }

    private function getHeaders(): array
    {
        if (function_exists("apache_request_headers") && $headers = apache_request_headers()) {
            return $headers;
        }

        $headers = array();

        // go through each server property and check if it is an HTTP header
        // remove the prefix and extract the name of the property in lowercase
        foreach (array_keys($_SERVER) as $skey) {
            if (strpos($skey, "HTTP_") === 0) {
                $headername = ucfirst(strtolower(str_replace("_", " ", substr($skey, 5))));
                $headers[$headername] = $_SERVER[$skey];
            }
        }

        return $headers;
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
