<?php

namespace Staffbase\plugins\sdk;

use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;
use Staffbase\plugins\sdk\Exceptions\SSOAuthenticationException;
use Staffbase\plugins\sdk\Exceptions\SSOException;
use Staffbase\plugins\sdk\SSOData\HeaderSSODataTrait;

class HeaderToken
{
    use HeaderSSODataTrait, SSOTokenTrait;

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
		if (!trim($appSecret)) {
			throw new SSOException('Parameter appSecret for SSOToken is empty.');
		}

		if (!trim($tokenData)) {
			throw new SSOException('Parameter tokenData for SSOToken is empty.');
		}

		$this->signerKey = $this->getKey(trim($appSecret));
		$this->config = Configuration::forSymmetricSigner(new Sha256(), $this->signerKey);

		$constrains = [
			new StrictValidAt(SystemClock::fromUTC(), $this->getLeewayInterval($leeway)),
			new SignedWith(new Sha256(), $this->signerKey),
		];

		$this->parseToken($tokenData, $constrains);
	}
}
