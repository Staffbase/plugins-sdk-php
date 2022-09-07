<?php
declare(strict_types=1);

/**
 * Trait to with specific claims of a JWT token in the request header.
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
 * Trait to with specific claims of a JWT token in the request header.
 */
trait HeaderSSOClaimsTrait
{
    public static string $CLAIM_BRANCH_ID = "branchId";
    public static string $CLAIM_USER_ID = "userId";
    public static string $CLAIM_TOKEN_ID = "tokenId";
}
