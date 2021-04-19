<?php
/**
 * SSO token implementation, based on this doc:
 * https://developers.staffbase.com/api/plugin-sso/
 *
 * PHP version 5.5.9
 *
 * @category  Authentication
 * @copyright 2017-2019 Staffbase, GmbH.
 * @author    Vitaliy Ivanov
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 * @link      https://github.com/staffbase/plugins-sdk-php
 */

namespace Staffbase\plugins\sdk;

use DateInterval;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Staffbase\plugins\sdk\Exceptions\SSOException;
use Staffbase\plugins\sdk\Exceptions\SSOAuthenticationException;
use Staffbase\plugins\sdk\Validation\HasInstanceId;

/**
 * A container which is able to decrypt and store the data transmitted
 * from Staffbase app to a plugin using the Staffbase single-sign-on.
 */
class SSOToken extends SSOData
{
	/**
	 * @var Token $token
	 */
	private ?Token $token = null;

	/**
	 * @var Key $key
	 */
	private Key $key;

	/**
	 * @var Configuration $config
	 */
	private Configuration $config;
	/**
	 * Constructor
	 *
	 * @param string $appSecret Either a PEM key or a file:// URL.
	 * @param string $tokenData The token text.
	 * @param int $leeway count of seconds added to current timestamp
	 *
	 * @throws SSOException on invalid parameters.
	 */
	public function __construct($appSecret, $tokenData, $leeway = 0) {

		if (!trim($appSecret))
			throw new SSOException('Parameter appSecret for SSOToken is empty.');

		if (!trim($tokenData))
			throw new SSOException('Parameter tokenData for SSOToken is empty.');

		if (!is_numeric($leeway))
			throw new SSOException('Parameter leeway has to be numeric.');

		$this->key = $this->getKey(trim($appSecret));
		$this->config = Configuration::forSymmetricSigner(new Sha256(), $this->key);

		$this->parseToken($tokenData, $leeway);
	}

	/**
	 * Creates and validates an SSO token.
	 *
	 * @param string $tokenData The token text.
	 * @param int $leeway count of seconds added to current timestamp
	 *
	 * @throws SSOAuthenticationException if the parsing/verification/validation of the token fails.
	 */
	protected function parseToken($tokenData, $leeway) {
		// parse text
		$this->token = $this->config->parser()->parse($tokenData);

		$constrains = [
			new StrictValidAt(SystemClock::fromUTC(), $this->getLeewayInterval($leeway)),
			new SignedWith(new Sha256(),$this->key),
			new HasInstanceId()
		];

		try {
			$this->config->validator()->assert($this->token, ...$constrains);
		} catch (RequiredConstraintsViolated $violation) {
			throw new SSOAuthenticationException($violation->getMessage());
		}
	}

	/**
	 * Test if a claim is set.
	 *
	 * @param string $claim name.
	 *
	 * @return boolean
	 */
	protected function hasClaim($claim) {
		return $this->token->claims()->has($claim);
	}

	/**
	 * Get a claim without checking for existence.
	 *
	 * @param string $claim name.
	 *
	 * @return mixed
	 */
	protected function getClaim($claim) {
		return $this->token->claims()->get($claim);
	}

	/**
	 * Get an array of all available claims and their values.
	 *
	 * @return array
	 */
	protected function getAllClaims() {

		return $this->token->claims()->all();
	}

	/**
	 * Translate a base64 string to PEM encoded public key.
	 *
	 * @param string $data base64 encoded key
	 *
	 * @return string PEM encoded key
	 */
	public static function base64ToPEMPublicKey($data) {

		$data = strtr($data, array(
			"\r" => "",
			"\n" => ""
		));

		return
			"-----BEGIN PUBLIC KEY-----\n".
			chunk_split($data, 64, "\n").
			"-----END PUBLIC KEY-----\n";
	}

	/**
	 * Decides between the new key methods, the JWT library offers
	 *
	 * @param string $appSecret
	 * @return Key
	 */
	private function getKey(string $appSecret) {
		if(strpos($appSecret,'-----') === 0 ) {
			$key = InMemory::plainText($appSecret);
		} else if (strpos($appSecret, 'file://') === 0 ) {
			$key = InMemory::file($appSecret);
		} else {
			$key = InMemory::plainText($this->base64ToPEMPublicKey($appSecret));
		}
		return $key;
	}

	/**
	 * Formats the leeway integer value into a DateInterval
	 *
	 * @param int $leeway
	 * @return DateInterval
	 */
	private function getLeewayInterval (int $leeway) {
		$leewayInterval = "PT{$leeway}S";

		try {
			$interval = new DateInterval($leewayInterval);
		} catch (\Exception $e) {
			error_log("Wrong date interval $leewayInterval");
			$interval = new DateInterval('PT0S');
		}

		return $interval;
	}
}
