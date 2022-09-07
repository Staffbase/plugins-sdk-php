<?php
declare(strict_types=1);

/**
 * Trait to access the specific claims of a JWT token in the request header.
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

/**
 * Trait to access the specific claims of a JWT token in the request header.
 */
trait HeaderSSODataTrait
{
    use SharedDataTrait, HeaderSSOClaimsTrait, ClaimAccessTrait;

    /**
     * Get the branch id of the app that issued the token.
     *
     * The id will always be present.
     *
     * @return string
     */
    public function getBranchId(): string
    {
        return $this->getClaimSafe(self::$CLAIM_BRANCH_ID);
    }

    /**
     * Get the user id of the app that issued the token.
     *
     * @return string|null
     */
    public function getUserId(): ?string
    {
        return $this->getClaimSafe(self::$CLAIM_USER_ID);
    }

    /**
     * Get the token id of the app that issued the token.
     *
     * @return string|null
     */
    public function getTokenId(): ?string
    {
        return $this->getClaimSafe(self::$CLAIM_TOKEN_ID);
    }
}
