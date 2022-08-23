<?php
/**
 * SSO token generator, based on this doc:
 * https://developers.staffbase.com/api/plugin-sso/
 *
 * PHP version 7.4.0
 *
 * @category  Authentication
 * @copyright 2017-2021 Staffbase, GmbH.
 * @author    Daniel Grosse
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 * @link      https://github.com/staffbase/plugins-sdk-php
 */

namespace Staffbase\plugins\sdk;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Token\RegisteredClaims;

class SSOTokenGenerator
{
    /**
     * Create a signed token from an array.
     *
     * Can be used in development in conjunction with getTokenData.
     *
     * @param string $privateKey private key
     * @param array $tokenData associative array of claims
     *
     * @return string Encoded token.
     */
    public static function createSignedTokenFromData(string $privateKey, array $tokenData): string
    {

        $config = Configuration::forSymmetricSigner(new Sha256(), InMemory::plainText($privateKey));
        return self::buildToken($config, $tokenData)->toString();
    }

    /**
     * Create an unsigned token by omitting sign().
     *
     * @param array $tokenData associative array of claims
     *
     * @return string Encoded token.
     */
    public static function createUnsignedTokenFromData(array $tokenData): string
    {

        $config = Configuration::forUnsecuredSigner();
        return self::buildToken($config, $tokenData)->toString();
    }

    /**
     * @param Configuration $config
     * @param array $tokenData
     * @return Token
     */
    private static function buildToken(Configuration $config, array $tokenData): Token
    {
        $builder = $config->builder();
        $token = $builder
            ->permittedFor($tokenData[SSOToken::$CLAIM_AUDIENCE])
            ->issuedAt($tokenData[SSOToken::$CLAIM_ISSUED_AT])
            ->canOnlyBeUsedAfter($tokenData[SSOToken::$CLAIM_NOT_BEFORE])
            ->expiresAt($tokenData[SSOToken::$CLAIM_EXPIRE_AT]);

        if (isset($tokenData[SSOToken::$CLAIM_ISSUER])) {
            $token->issuedBy($tokenData[SSOToken::$CLAIM_ISSUER]);
        }

        if (isset($tokenData[SSOToken::$CLAIM_USER_ID])) {
            $token->relatedTo($tokenData[SSOToken::$CLAIM_USER_ID]);
        }

        if (isset($tokenData[SSOToken::$CLAIM_JWT_ID])) {
            $token->identifiedBy($tokenData[SSOToken::$CLAIM_JWT_ID]);
        }

        // Remove all set keys as they throw an exception when used with withClaim
        $claims = array_filter(
            $tokenData,
            fn ($key) => !in_array($key, RegisteredClaims::ALL),
            ARRAY_FILTER_USE_KEY
        );

        foreach ($claims as $claim => $value) {
            $builder->withClaim($claim, $value);
        }

        return $token->getToken($config->signer(), $config->signingKey());
    }
}
