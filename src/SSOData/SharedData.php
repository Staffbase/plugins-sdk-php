<?php

namespace Staffbase\plugins\sdk\SSOData;

use DateTimeImmutable;

trait SharedData {

	use ClaimAccess;

	public static string $CLAIM_AUDIENCE                    = 'aud';
	public static string $CLAIM_EXPIRE_AT                   = 'exp';
	public static string $CLAIM_JWT_ID		         	    = 'jti';
	public static string $CLAIM_ISSUED_AT                   = 'iat';
	public static string $CLAIM_ISSUER                      = 'iss';
	public static string $CLAIM_NOT_BEFORE                  = 'nbf';
	public static string $CLAIM_SUBJECT                     = 'sub';

	public static string $CLAIM_USER_ROLE                   = 'role';

	/**
	 * Get targeted audience of the token. Currently only
	 * one audience is supported.
	 *
	 * @return null|string
	 */
	public function getAudience(): ?string
	{

		$audience = $this->getClaimSafe(self::$CLAIM_AUDIENCE);

		return !is_array($audience) ? $audience : $audience[0] ?? null;
	}

	/**
	 * Get the time when the token expires.
	 *
	 * @return DateTimeImmutable
	 */
	public function getExpireAtTime(): ?DateTimeImmutable
	{
		return $this->getClaimSafe(self::$CLAIM_EXPIRE_AT);
	}

	/**
	 * Get the time when the token starts to be valid.
	 *
	 * @return DateTimeImmutable
	 */
	public function getNotBeforeTime(): ?DateTimeImmutable
	{
		return $this->getClaimSafe(self::$CLAIM_NOT_BEFORE);
	}

	/**
	 * Get the time when the token was issued.
	 *
	 * @return DateTimeImmutable
	 */
	public function getIssuedAtTime(): ?DateTimeImmutable
	{
		return $this->getClaimSafe(self::$CLAIM_ISSUED_AT);
	}

	/**
	 * Get issuer of the token.
	 *
	 * @return null|string
	 */
	public function getIssuer(): ?string
	{
		return $this->getClaimSafe(self::$CLAIM_ISSUER);
	}

	/**
	 * Get the id of the token
	 *
	 * @return string|null
	 */
	public function getId(): ?string
	{
		return $this->getClaimSafe(self::$CLAIM_JWT_ID);
	}

	/**
	 * Get the id of the token
	 *
	 * @return string|null
	 */
	public function getSubject(): ?string
	{
		return $this->getClaimSafe(self::$CLAIM_SUBJECT);
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
		return $this->getClaimSafe(self::$CLAIM_USER_ROLE);
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
}
