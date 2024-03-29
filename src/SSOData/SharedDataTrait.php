<?php
declare(strict_types=1);

/**
 * Trait to access the shared claims of a JWT token.
 *
 * PHP version 7.4
 *
 * @category  Token
 * @copyright 2017-2022 Staffbase, GmbH.
 * @author    Daniel Grosse
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 * @link      https://github.com/staffbase/plugins-sdk-php
 */

namespace Staffbase\plugins\sdk\SSOData;

use DateTimeImmutable;
use TypeError;

/**
 * Trait to access the shared claims of a JWT token.
 */
trait SharedDataTrait
{

    use ClaimAccessTrait;

    private static string $userRoleEditor = 'editor';

    private static string $remoteCallDelete = 'delete';

    /**
     * Get targeted audience of the token. Currently only
     * one audience is supported.
     *
     * @return null|string
     */
    public function getAudience(): ?string
    {
        /** @var array|string|null $audience */
        $audience = $this->getClaimSafe(SharedClaimsInterface::CLAIM_AUDIENCE);

        if (is_array($audience)) {
            $audience = current($audience);
        }

        if (is_string($audience) || is_null($audience)) {
            return $audience;
        }

        throw new TypeError('Audience must be of the type string or null, got: ' . gettype($audience));
    }

    /**
     * Get the time when the token expires.
     *
     * @return DateTimeImmutable
     */
    public function getExpireAtTime(): ?DateTimeImmutable
    {
        return $this->getClaimSafe(SharedClaimsInterface::CLAIM_EXPIRE_AT);
    }

    /**
     * Get the time when the token starts to be valid.
     *
     * @return DateTimeImmutable
     */
    public function getNotBeforeTime(): ?DateTimeImmutable
    {
        return $this->getClaimSafe(SharedClaimsInterface::CLAIM_NOT_BEFORE);
    }

    /**
     * Get the time when the token was issued.
     *
     * @return DateTimeImmutable
     */
    public function getIssuedAtTime(): ?DateTimeImmutable
    {
        return $this->getClaimSafe(SharedClaimsInterface::CLAIM_ISSUED_AT);
    }

    /**
     * Get issuer of the token.
     *
     * @return null|string
     */
    public function getIssuer(): ?string
    {
        return $this->getClaimSafe(SharedClaimsInterface::CLAIM_ISSUER);
    }

    /**
     * Get the id of the token
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->getClaimSafe(SharedClaimsInterface::CLAIM_JWT_ID);
    }

    /**
     * Get the id of the token
     *
     * @return string|null
     */
    public function getSubject(): ?string
    {
        return $this->getClaimSafe(SharedClaimsInterface::CLAIM_SUBJECT);
    }

    /**
     * Get the role of the accessing user.
     *
     * If this is set to “editor”, the requesting user may manage the contents
     * of the plugin instance, i.e. she has administration rights.
     * The type of the accessing entity can be either a “user” or a “editor”.
     *
     * @return null|string
     */
    public function getRole(): ?string
    {
        return $this->getClaimSafe(SharedClaimsInterface::CLAIM_USER_ROLE);
    }

    /**
     * Get all stored data.
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->getAllClaims();
    }

    /**
     * Check if the SSO call is an instance deletion call.
     *
     * If an editor deletes a plugin instance in Staffbase,
     * this will be true.
     *
     * @return boolean
     */
    public function isDeleteInstanceCall(): bool
    {
        return $this->getUserId() === self::$remoteCallDelete;
    }

    /**
     * Check if the user is an editor.
     *
     * Only when the editor role is explicitly
     * provided the user will be marked as editor.
     *
     * @return boolean
     */
    public function isEditor(): bool
    {
        return $this->getClaimSafe(SharedClaimsInterface::CLAIM_USER_ROLE) === self::$userRoleEditor;
    }
}
