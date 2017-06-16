<?php
/**
 * SSO token implementation, based on this doc:
 * https://developers.staffbase.com/api/plugin-sso/
 *
 * PHP version 5.5.9
 *
 * @category  Authentication
 * @copyright 2017 Staffbase, GmbH. 
 * @author    Vitaliy Ivanov
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 * @link      https://github.com/staffbase/plugins-sdk-php
 */

namespace Staffbase\plugins\sdk;

use Exception;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\ValidationData;
use Lcobucci\JWT\Signer\Keychain;
use Lcobucci\JWT\Signer\Rsa\Sha256;

/**
 * A container which is able to decrypt and store the data transmitted
 * from Staffbase app to a plugin using the Staffbase single-sign-on.
 */
class SSOToken extends SSOData {

	/** 
	 * @var $token  Lcobucci\JWT\Token 
	 */
	private $token = null;

	/**
	 * Constructor
	 *
	 * @param string $appSecret Either a PEM key or a file:// URL.
	 * @param string $tokenData The token text.
	 *
	 * @throws Exception On empty parameters.
	 */
	public function __construct($appSecret, $tokenData) {

		if (!trim($appSecret))
			throw new Exception('Parameter appSecret for SSOToken is empty.');

		if (!trim($tokenData))
			throw new Exception('Parameter tokenData for SSOToken is empty.');

		$this->parseToken($appSecret, $tokenData);
	}

	/**
	 * Creates and validates an SSO token.
	 *
	 * @param string $appSecret Either a PEM formatted key or a file:// URL of the same.
	 * @param string $tokenData The token text.
	 *
	 * @return Lcobucci\JWT\Token;
	 *
	 * @throws Exception if the parsing/verification/validation of the token fails.
	 */
	protected function parseToken($appSecret, $tokenData) {
		// parse text
		$this->token = (new Parser())->parse((string) $tokenData);

		// verify signature
		$signer = new Sha256();
		$keychain = new Keychain();

		if (!$this->token->verify($signer, $keychain->getPublicKey($appSecret)))
			throw new Exception('Token verification failed.');

		// validate claims
		$data = new ValidationData(); // iat, nbf and exp are validated by default

		if (!$this->token->validate($data))
			throw new Exception('Token Validation failed.');

		// its a security risk to work with tokens lacking instance id
		if (!trim($this->getInstanceId()))
			throw new Exception('Token lacks instance id.');
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