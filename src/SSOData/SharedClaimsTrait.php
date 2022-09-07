<?php
declare(strict_types=1);

/**
 * Trait to with shared claims of a JWT token.
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
 * Trait to with shared claims of a JWT token.
 */
trait SharedClaimsTrait
{
    public static string $CLAIM_AUDIENCE                    = 'aud';
    public static string $CLAIM_EXPIRE_AT                   = 'exp';
    public static string $CLAIM_JWT_ID                      = 'jti';
    public static string $CLAIM_ISSUED_AT                   = 'iat';
    public static string $CLAIM_ISSUER                      = 'iss';
    public static string $CLAIM_NOT_BEFORE                  = 'nbf';
    public static string $CLAIM_SUBJECT                     = 'sub';

    public static string $CLAIM_USER_ROLE                   = 'role';
}
