<?php
declare(strict_types=1);

/**
 * Interface with shared claims of a JWT token.
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
 * Interface with shared claims of a JWT token.
 */
interface SharedClaimsInterface
{
    public const CLAIM_AUDIENCE                    = 'aud';
    public const CLAIM_EXPIRE_AT                   = 'exp';
    public const CLAIM_JWT_ID                      = 'jti';
    public const CLAIM_ISSUED_AT                   = 'iat';
    public const CLAIM_ISSUER                      = 'iss';
    public const CLAIM_NOT_BEFORE                  = 'nbf';
    public const CLAIM_SUBJECT                     = 'sub';

    public const CLAIM_USER_ROLE                   = 'role';
}
