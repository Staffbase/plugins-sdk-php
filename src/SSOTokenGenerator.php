<?php

declare(strict_types=1);

/**
 * SSO token generator, based on this doc:
 * https://developers.staffbase.com/api/plugin-sso/
 *
 * @category  Authentication
 * @copyright 2017-2025 Staffbase SE.
 * @author    Daniel Grosse
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 * @link      https://github.com/staffbase/plugins-sdk-php
 */

namespace Staffbase\plugins\sdk;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Token\RegisteredClaims;

class SSOTokenGenerator
{
    /**
     * @param string $privateKey private key
     * @param array<string,mixed> $tokenData associative array of claims
     * @param Signer|null $signer the Signer instance to sign the token, defaults to SHA256
     *
     * @return string Encoded token.
     */
    public static function createSignedTokenFromData(string $privateKey, array $tokenData, ?Signer $signer = null): string
    {

        if (!trim($privateKey)) {
            throw new \InvalidArgumentException('Parameter privateKey for token generation is empty.');
        }

        // After validation, we know $privateKey is non-empty
        /** @var non-empty-string $privateKey */
        $config = Configuration::forSymmetricSigner($signer ?: new Sha256(), InMemory::plainText($privateKey));
        return self::buildToken($config, $tokenData)->toString();
    }

    /**
     * @param Configuration $config
     * @param array<string,mixed> $tokenData
     * @return Token
     */
    private static function buildToken(Configuration $config, array $tokenData): Token
    {
        $builder = $config->builder();
        $token = $builder
            ->permittedFor($tokenData[SSOData\SharedClaimsInterface::CLAIM_AUDIENCE])
            ->issuedAt($tokenData[SSOData\SharedClaimsInterface::CLAIM_ISSUED_AT])
            ->canOnlyBeUsedAfter($tokenData[SSOData\SharedClaimsInterface::CLAIM_NOT_BEFORE])
            ->expiresAt($tokenData[SSOData\SharedClaimsInterface::CLAIM_EXPIRE_AT]);

        if (isset($tokenData[SSOData\SharedClaimsInterface::CLAIM_ISSUER])) {
            $token = $token->issuedBy($tokenData[SSOData\SharedClaimsInterface::CLAIM_ISSUER]);
        }

        if (isset($tokenData[SSOData\SSODataClaimsInterface::CLAIM_USER_ID])) {
            $token = $token->relatedTo($tokenData[SSOData\SSODataClaimsInterface::CLAIM_USER_ID]);
        }

        if (isset($tokenData[SSOData\SharedClaimsInterface::CLAIM_JWT_ID])) {
            $token = $token->identifiedBy($tokenData[SSOData\SharedClaimsInterface::CLAIM_JWT_ID]);
        }

        // Remove all set keys as they throw an exception when used with withClaim
        $claims = array_filter(
            $tokenData,
            static fn($key) => !in_array($key, RegisteredClaims::ALL, true),
            ARRAY_FILTER_USE_KEY,
        );

        foreach ($claims as $claim => $value) {
            if (empty($claim)) {
                continue;
            }
            $token = $token->withClaim($claim, $value);
        }

        return $token->getToken($config->signer(), $config->signingKey());
    }
}
