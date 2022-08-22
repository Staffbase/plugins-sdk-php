<?php

namespace Staffbase\plugins\sdk\SSOData;

use DateTimeImmutable;

abstract class SharedData {

	private const CLAIM_AUDIENCE                    = 'aud';
	private const CLAIM_EXPIRE_AT                   = 'exp';
	private const CLAIM_JWT_ID                 	    = 'jti';
	private const CLAIM_ISSUED_AT                   = 'iat';
	private const CLAIM_ISSUER                      = 'iss';
	private const CLAIM_NOT_BEFORE                  = 'nbf';
	private const CLAIM_SUBJECT                     = 'sub';

	private const CLAIM_USER_ROLE                   = 'role';

	/**
	 * Test if a claim is set.
	 *
	 * @param string $claim name.
	 *
	 * @return boolean
	 */
	abstract protected function hasClaim(string $claim): bool;

	/**
	 * Get a claim without checking for existence.
	 *
	 * @param string $claim name.
	 *
	 * @return mixed
	 */
	abstract protected function getClaim(string $claim);

	/**
	 * Get an array of all available claims and their values.
	 *
	 * @return array
	 */
	abstract protected function getAllClaims(): array;

	/**
	 * Internal getter for all token properties.
	 *
	 * Has a check for undefined claims to make getter calls always valid.
	 *
	 * @param string Name of the claim.
	 *
	 * @return mixed
	 */
	protected function getClaimSafe(string $name) {

		if ($this->hasClaim($name)) {
			return $this->getClaim($name);
		}

		return null;
	}

	/**
	 * Get targeted audience of the token. Currently only
	 * one audience is supported.
	 *
	 * @return null|string
	 */
	public function getAudience(): ?string
	{

		$audience = $this->getClaimSafe(self::CLAIM_AUDIENCE);

		return !is_array($audience) ? $audience : $audience[0] ?? null;
	}

	/**
	 * Get the time when the token expires.
	 *
	 * @return DateTimeImmutable
	 */
	public function getExpireAtTime(): ?DateTimeImmutable
	{
		return $this->getClaimSafe(self::CLAIM_EXPIRE_AT);
	}

	/**
	 * Get the time when the token starts to be valid.
	 *
	 * @return DateTimeImmutable
	 */
	public function getNotBeforeTime(): ?DateTimeImmutable
	{
		return $this->getClaimSafe(self::CLAIM_NOT_BEFORE);
	}

	/**
	 * Get the time when the token was issued.
	 *
	 * @return DateTimeImmutable
	 */
	public function getIssuedAtTime(): ?DateTimeImmutable
	{
		return $this->getClaimSafe(self::CLAIM_ISSUED_AT);
	}

	/**
	 * Get issuer of the token.
	 *
	 * @return null|string
	 */
	public function getIssuer(): ?string
	{
		return $this->getClaimSafe(self::CLAIM_ISSUER);
	}

	/**
	 * Get the id of the token
	 *
	 * @return string|null
	 */
	public function getId(): ?string
	{
		return $this->getClaimSafe(self::CLAIM_JWT_ID);
	}

	/**
	 * Get the id of the token
	 *
	 * @return string|null
	 */
	public function getSubject(): ?string
	{
		return $this->getClaimSafe(self::CLAIM_SUBJECT);
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
		return $this->getClaimSafe(self::CLAIM_USER_ROLE);
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
