<?php
declare(strict_types=1);

namespace Staffbase\plugins\sdk;

use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;
use Lcobucci\JWT\Validation\ValidAt;
use Staffbase\plugins\sdk\Exceptions\SSOAuthenticationException;
use Staffbase\plugins\sdk\Exceptions\SSOException;

abstract class AbstractToken
{

    use SSOTokenTrait;

    /**
     * Constructor
     *
     * @param string $appSecret Either a PEM key or a file:// URL.
     * @param string $tokenData The token text.
     * @param int|null $leeway count of seconds added to current timestamp
     * @param ValidAt[] constrains
     *
     * @throws SSOAuthenticationException
     * @throws SSOException on invalid parameters.
     */
    public function __construct(string $appSecret, string $tokenData, ?int $leeway = 0, array $constrains = [])
    {
        if (!trim($appSecret)) {
            throw new SSOException('Parameter appSecret for SSOToken is empty.');
        }

        if (!trim($tokenData)) {
            throw new SSOException('Parameter tokenData for SSOToken is empty.');
        }

        $this->setSignerKey(trim($appSecret));
        $this->setConfig(Configuration::forSymmetricSigner(new Sha256(), $this->getSignerKey()));

        $defaultConstrains = [
            new StrictValidAt(SystemClock::fromUTC(), $this->getLeewayInterval($leeway)),
            new SignedWith(new Sha256(), $this->getSignerKey()),
        ];

        $this->parseToken($tokenData, array_merge($defaultConstrains, $constrains));
    }
}