<?php
declare(strict_types=1);

/**
 * SSO token implementation, based on this doc:
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

use DateInterval;
use Exception;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use Lcobucci\JWT\Validation\ValidAt;
use Staffbase\plugins\sdk\Exceptions\SSOAuthenticationException;

/**
 * A container which is able to decrypt and store the data transmitted
 * from Staffbase app to a plugin using the Staffbase single-sign-on.
 */
trait SSOTokenTrait
{
    /**
     * @var Token | null $token
     */
    private ?Token $token = null;

    /**
     * @var Key $signerKey
     */
    private Key $signerKey;

    /**
     * @var Configuration $config
     */
    private Configuration $config;

    /**
     * Creates and validates an SSO token.
     *
     * @param string $tokenData The token text.
	 * @param ValidAt[] $constrains an array of validation instances
     *
     * @throws SSOAuthenticationException if the parsing/verification/validation of the token fails.
     */
    protected function parseToken(string $tokenData, array $constrains = []): void
    {
        // parse text
        $this->token = $this->config->parser()->parse($tokenData);

        try {
            $this->config->validator()->assert($this->token, ...$constrains);
        } catch (RequiredConstraintsViolated $violation) {
            throw new SSOAuthenticationException($violation->getMessage());
        }
    }

    /**
     * Test if a claim is set.
     *
     * @param string $claim name.
     *
     * @return boolean
     */
    protected function hasClaim(string $claim): bool
    {
        return $this->token->claims()->has($claim);
    }

    /**
     * Get a claim without checking for existence.
     *
     * @param string $claim name.
     *
     * @return mixed
     */
    protected function getClaim(string $claim)
    {
        return $this->token->claims()->get($claim);
    }

    /**
     * Get an array of all available claims and their values.
     *
     * @return array
     */
    protected function getAllClaims(): array
    {

        return $this->token->claims()->all();
    }

    /**
     * Translate a base64 string to PEM encoded public key.
     *
     * @param string $data base64 encoded key
     *
     * @return string PEM encoded key
     */
    public static function base64ToPEMPublicKey(string $data): string
    {

        $data = strtr($data, array(
            "\r" => "",
            "\n" => ""
        ));

        return
            "-----BEGIN PUBLIC KEY-----\n".
            chunk_split($data, 64).
            "-----END PUBLIC KEY-----\n";
    }

    /**
     * Decides between the new key methods, the JWT library offers
     *
     * @param string $appSecret
     * @return Key
     */
    private function getKey(string $appSecret): Key
    {
        if (strpos($appSecret, '-----') === 0) {
            $key = InMemory::plainText($appSecret);
        } elseif (strpos($appSecret, 'file://') === 0) {
            $key = InMemory::file($appSecret);
        } else {
            $key = InMemory::plainText(self::base64ToPEMPublicKey($appSecret));
        }
        return $key;
    }

    /**
     * Formats the leeway integer value into a DateInterval as this is
     * needed by the JWT library
     *
     * @param int $leeway count of seconds added to current timestamp
     * @return DateInterval DateInterval
     */
    private function getLeewayInterval(int $leeway): DateInterval
    {
        $leewayInterval = "PT{$leeway}S";

        try {
            $interval = new DateInterval($leewayInterval);
        } catch (Exception $e) {
            error_log("Wrong date interval $leewayInterval");
            $interval = new DateInterval('PT0S');
        }

        return $interval;
    }
}
