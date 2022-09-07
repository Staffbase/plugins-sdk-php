<?php
/**
 * JWT Token validation
 *
 * PHP version 7.4
 *
 * @category  Validation
 * @copyright 2017-2022 Staffbase, GmbH.
 * @author    Daniel Grosse
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 * @link      https://github.com/staffbase/plugins-sdk-php
 */

namespace Staffbase\plugins\sdk\Validation;

use Lcobucci\JWT\Token;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint;
use Lcobucci\JWT\Validation\ConstraintViolation;
use Staffbase\plugins\sdk\SSOToken;

final class HasInstanceId implements Constraint
{
    /**
     * @inheritDoc
     */
    public function assert(Token $token): void
    {
        if (!$token instanceof UnencryptedToken) {
            throw new ConstraintViolation('You should pass a plain token');
        }

        if (!$this->hasInstanceId($token)) {
            throw new ConstraintViolation('Token lacks instance id.');
        }
    }

    private function hasInstanceId(Token $token): bool
    {
        return (bool) $token->claims()->get(SSOToken::$CLAIM_INSTANCE_ID);
    }
}
