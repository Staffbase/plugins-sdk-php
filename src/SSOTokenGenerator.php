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
        // Validate and coerce required registered claims to the expected types
        $audience = $tokenData[SSOData\SharedClaimsInterface::CLAIM_AUDIENCE] ?? '';
        if (!is_string($audience) || $audience === '') {
            throw new \InvalidArgumentException('aud claim must be a non-empty string for token generation');
        }

        $issuedAt = $tokenData[SSOData\SharedClaimsInterface::CLAIM_ISSUED_AT] ?? null;
        if (!($issuedAt instanceof \DateTimeImmutable)) {
            throw new \InvalidArgumentException('iat claim must be a DateTimeImmutable for token generation');
        }

        $notBefore = $tokenData[SSOData\SharedClaimsInterface::CLAIM_NOT_BEFORE] ?? null;
        if (!($notBefore instanceof \DateTimeImmutable)) {
            throw new \InvalidArgumentException('nbf claim must be a DateTimeImmutable for token generation');
        }

        $expiresAt = $tokenData[SSOData\SharedClaimsInterface::CLAIM_EXPIRE_AT] ?? null;
        if (!($expiresAt instanceof \DateTimeImmutable)) {
            throw new \InvalidArgumentException('exp claim must be a DateTimeImmutable for token generation');
        }

        $token = $builder
            ->permittedFor($audience)
            ->issuedAt($issuedAt)
            ->canOnlyBeUsedAfter($notBefore)
            ->expiresAt($expiresAt);

        if (isset($tokenData[SSOData\SharedClaimsInterface::CLAIM_ISSUER])) {
            $issuer = $tokenData[SSOData\SharedClaimsInterface::CLAIM_ISSUER];
            if (is_string($issuer) && $issuer !== '') {
                $token = $token->issuedBy($issuer);
            }
        }

        if (isset($tokenData[SSOData\SSODataClaimsInterface::CLAIM_USER_ID])) {
            $subject = $tokenData[SSOData\SSODataClaimsInterface::CLAIM_USER_ID];
            if (is_string($subject) && $subject !== '') {
                $token = $token->relatedTo($subject);
            }
        }

        if (isset($tokenData[SSOData\SharedClaimsInterface::CLAIM_JWT_ID])) {
            $jwtId = $tokenData[SSOData\SharedClaimsInterface::CLAIM_JWT_ID];
            if (is_string($jwtId) && $jwtId !== '') {
                $token = $token->identifiedBy($jwtId);
            }
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
