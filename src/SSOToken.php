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

use Lcobucci\JWT\Token;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\ValidationData;
use Lcobucci\JWT\Claim\Validatable;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Staffbase\plugins\sdk\Exceptions\SSOException;
use Staffbase\plugins\sdk\Exceptions\SSOAuthenticationException;

/**
 * A container which is able to decrypt and store the data transmitted
 * from Staffbase app to a plugin using the Staffbase single-sign-on.
 */
class SSOToken extends SSOData
{
	/**
	 * @var Token $token
	 */
	private $token = null;

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

		// convert secret to PEM if its a plain base64 string and does not yield an url
		if(strpos(trim($appSecret),'-----') !== 0 && strpos(trim($appSecret), 'file://') !==0 )
			$appSecret = self::base64ToPEMPublicKey($appSecret);

		$this->parseToken($appSecret, $tokenData, $leeway);
	}

	/**
	 * Creates and validates an SSO token.
	 *
	 * @param string $appSecret Either a PEM formatted key or a file:// URL of the same.
	 * @param string $tokenData The token text.
	 * @param int $leeway count of seconds added to current timestamp
	 *
	 * @throws SSOAuthenticationException if the parsing/verification/validation of the token fails.
	 */
	protected function parseToken($appSecret, $tokenData, $leeway) {

		// parse text
		$this->token = (new Parser())->parse((string) $tokenData);

		// verify signature
		$signer = new Sha256();
		$key = new Key($appSecret);

		if (!$this->token->verify($signer, $key))
			throw new SSOAuthenticationException('Token verification failed.');

		// validate claims
		$data = new ValidationData(time(), $leeway); // iat, nbf and exp are validated by default

		if (!$this->token->validate($data)) {
			$this->throwVerboseException($data);
		}

		// its a security risk to work with tokens lacking instance id
		if (!trim($this->getInstanceId()))
			throw new SSOAuthenticationException('Token lacks instance id.');
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
	 * Validate the token with more verbose exceptions
	 *
	 * Due to minor shortcomings of the library we have to redo the validation
	 * manually to get the reason for the failure and propagate it.
	 * We emulate the validation process for the v3.x of the library.
	 *
	 * This will most likely have to change on library upgrade either
	 * by using then supported verbosity or reimplementing validation
	 * as done in the new flow.
	 *
	 * @param ValidationData $data to validate against
	 *
	 * @throws SSOAuthenticationException always.
	 */
	protected function throwVerboseException(ValidationData $data) {

		foreach ($this->token->getClaims() as $claim) {
			if ($claim instanceof Validatable) {
				if (!$claim->validate($data)) {

					$claimName  = $claim->getName();
					$claimValue = $claim->getValue();

					// get the short class-name of the validatable claim
					$segments = explode('\\', get_class($claim));
					$operator = array_pop($segments);
					$operand  = $data->get($claimName);

					throw new SSOAuthenticationException("Token Validation failed on claim '$claimName' $claimValue $operator $operand.");
				}
			}
		}

		// unknown reason, probably an addition to used library
		throw new SSOAuthenticationException('Token Validation failed.');
	}

	/**
	 * Test if a claim is set.
	 *
	 * @param string $claim name.
	 *
	 * @return boolean
	 */
	protected function hasClaim($claim) {

		return $this->token->hasClaim($claim);
	}

	/**
	 * Get a claim without checking for existence.
	 *
	 * @param string $claim name.
	 *
	 * @return mixed
	 */
	protected function getClaim($claim) {

		return $this->token->getClaim($claim);
	}

	/**
	 * Get an array of all available claims and their values.
	 *
	 * @return array
	 */
	protected function getAllClaims() {

		$res = [];
		$claims = $this->token->getClaims();

		foreach($claims as $claim)
			$res[$claim->getName()] = $claim->getValue();

		return $res;
	}
}
