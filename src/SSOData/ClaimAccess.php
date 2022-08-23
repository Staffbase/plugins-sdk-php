<?php

namespace Staffbase\plugins\sdk\SSOData;

trait ClaimAccess {

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
}
