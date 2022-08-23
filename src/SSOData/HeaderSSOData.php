<?php

namespace Staffbase\plugins\sdk\SSOData;

abstract class HeaderSSOData extends SharedData
{
	private const CLAIM_BRANCH_ID = "branchId";
	private const CLAIM_USER_ID = "userId";
	private const CLAIM_TOKEN_ID = "tokenId";

	/**
	 * Get the branch id of the app that issued the token.
	 *
	 * The id will always be present.
	 *
	 * @return string
	 */
	public function getBranchId(): string
	{
		return $this->getClaimSafe(self::CLAIM_BRANCH_ID);
	}

	/**
	 * Get the user id of the app that issued the token.
	 *
	 * @return string|null
	 */
	public function getUserId(): ?string
	{
		return $this->getClaimSafe(self::CLAIM_USER_ID);
	}

	/**
	 * Get the token id of the app that issued the token.
	 *
	 * @return string|null
	 */
	public function getTokenId(): ?string
	{
		return $this->getClaimSafe(self::CLAIM_TOKEN_ID);
	}

}
