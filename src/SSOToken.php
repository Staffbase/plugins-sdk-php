<?php
declare(strict_types=1);

/**
 * SSO token parser and validator
 *
 * PHP version 7.4
 *
 * @category  Authentication
 * @copyright 2017-2022 Staffbase, GmbH.
 * @author    Daniel Grosse
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 * @link      https://github.com/staffbase/plugins-sdk-php
 */

namespace Staffbase\plugins\sdk;

use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;
use Staffbase\plugins\sdk\Exceptions\SSOAuthenticationException;
use Staffbase\plugins\sdk\Exceptions\SSOException;
use Staffbase\plugins\sdk\SSOData\SharedClaimsInterface;
use Staffbase\plugins\sdk\SSOData\SSODataClaimsInterface;
use Staffbase\plugins\sdk\SSOData\SSODataTrait;
use Staffbase\plugins\sdk\Validation\HasInstanceId;

/**
 * Class to parse and validate a JWT Token
 */
class SSOToken extends AbstractToken implements SharedClaimsInterface, SSODataClaimsInterface
{
    use SSODataTrait;

    /**
     * Constructor
     *
     * @param string $appSecret Either a PEM key or a file:// URL.
     * @param string $tokenData The token text.
     * @param int|null $leeway count of seconds added to current timestamp
     *
     * @throws SSOAuthenticationException
     * @throws SSOException on invalid parameters.
     */
    public function __construct(string $appSecret, string $tokenData, ?int $leeway = 0)
    {
        $constrains = [
            new StrictValidAt(SystemClock::fromUTC(), $this->getLeewayInterval((int) $leeway)),
            new HasInstanceId()
        ];

        $signer = new Sha256();

        parent::__construct($appSecret, $tokenData, $signer, $constrains);
    }
}
