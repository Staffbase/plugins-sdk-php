<?php

declare(strict_types=1);

namespace Staffbase\plugins\sdk;

use DateInterval;
use Exception;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Validation\Constraint;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use Staffbase\plugins\sdk\Exceptions\SSOAuthenticationException;
use Staffbase\plugins\sdk\Exceptions\SSOException;

abstract class AbstractToken
{
    private Token $token;

    private Key $signerKey;

    private Configuration $config;

    private array $constraints;

    /**
     * Constructor
     *
     * @param string $appSecret Either a PEM key or a file:// URL.
     * @param string $tokenData The token text.
     * @param Signer $signer The algorithm which is used to sign the token
     * @param Constraint[] $constrains constrains
     *
     * @throws SSOAuthenticationException
     * @throws SSOException on invalid parameters.
     */
    public function __construct(string $appSecret, protected string $tokenData, Signer $signer, array $constrains = [])
    {
        if (!trim($appSecret)) {
            throw new SSOException('Parameter appSecret for SSOToken is empty.');
        }

        if (!trim($tokenData)) {
            throw new SSOException('Parameter tokenData for SSOToken is empty.');
        }

        $this->setSignerKey(trim($appSecret));
        $this->setConfig(Configuration::forSymmetricSigner($signer, $this->getSignerKey()));

        $this->constraints = [
            new SignedWith($signer, $this->getSignerKey()),
            ...$constrains,
        ];
    }

    /**
     * Creates and validates an SSO token.
     *
     */
    protected function parseToken(): void
    {
        // parse text
        $this->token = $this->config->parser()->parse($this->tokenData);
    }

    /**
     * Creates and validates an SSO token.
     *
     * @throws SSOAuthenticationException if the parsing/verification/validation of the token fails.
     */
    protected function validateToken(): void
    {
        try {
            $this->config->validator()->assert($this->token, ...$this->constraints);
        } catch (RequiredConstraintsViolated $violation) {
            throw new SSOAuthenticationException($violation->getMessage());
        }
    }

    /**
     * Test if a claim is set.
     *
     * @param string $claim name.
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

        $data = strtr($data, [
            "\r" => "",
            "\n" => "",
        ]);

        return
            "-----BEGIN PUBLIC KEY-----\n" .
            chunk_split($data, 64) .
            "-----END PUBLIC KEY-----\n";
    }

    /**
     * Set the configuration
     *
     * @param Configuration $value
     */
    public function setConfig(Configuration $value): void
    {
        $this->config = $value;
    }

    /**
     * Get the configuration
     */
    public function getConfig(): Configuration
    {
        return $this->config;
    }

    /**
     * Creates a key from the secret and stores it to the property
     * @param string $secret
     */
    public function setSignerKey(string $secret): void
    {
        $this->signerKey = $this->getKey($secret);
    }

    /**
     * Get the Signer key
     */
    public function getSignerKey(): Key
    {
        return $this->signerKey;
    }

    /**
     * Decides between the new key methods, the JWT library offers
     *
     * @param string $appSecret
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
    protected function getLeewayInterval(int $leeway): DateInterval
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
