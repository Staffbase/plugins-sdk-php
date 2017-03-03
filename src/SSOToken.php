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
 * A container for the data transmitted from Staffbase app to a plugin 
 * using the Staffbase single-sign-on.
 */
class SSOToken {

	const CLAIM_AUDIENCE              = 'aud';
	const CLAIM_EXPIRE_AT             = 'exp';
	const CLAIM_NOT_BEFORE            = 'nbf';
	const CLAIM_ISSUED_AT             = 'iat';
	const CLAIM_ISSUER                = 'iss';
	const CLAIM_INSTANCE_ID           = 'instance_id';
	const CLAIM_INSTANCE_NAME         = 'instance_name';
	const CLAIM_USER_ID               = 'sub';
	const CLAIM_USER_EXTERNAL_ID      = 'external_id';
	const CLAIM_USER_FULL_NAME        = 'name';
	const CLAIM_USER_FIRST_NAME       = 'given_name';
	const CLAIM_USER_LAST_NAME        = 'family_name';
	const CLAIM_USER_ROLE             = 'role';
	const CLAIM_ENTITY_TYPE           = 'type';
	const CAIM_THEME_TEXT_COLOR       = 'theming_text';
	const CAIM_THEME_BACKGROUND_COLOR = 'theming_bg';
	const CAIM_USER_LOCALE            = 'locale';

	const USER_ROLE_USER = 'user';
	const USER_ROLE_EDITOR = 'editor';

	/** @var $token  Lcobucci\JWT\Token */
	private $token = null;

	/**
	 * Constructor
	 *
	 * @param string $appSecret Either a key or a file:// URL.
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
	 * @param string $appSecret Either a key or a file:// URL.
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
	 * Internal getter for all token properties.
	 *
	 * Has a check for undefined claims to make getter calls always valid.
	 *
	 * @param string Name of the claim.
	 *
	 * @return mixed
	 */
	protected function getClaim($name) {

		if ($this->token->hasClaim($name))
			return $this->token->getClaim($name);

		return null;
	}

	/**
	 * Get targeted audience of the token.
	 *
	 * @return null|string
	 */
	public function getAudience() {
		return $this->getClaim(self::CLAIM_AUDIENCE);
	}

	/**
	 * Get the time when the token expires.
	 *
	 * @return int
	 */
	public function getExpireAtTime() {
		return $this->getClaim(self::CLAIM_EXPIRE_AT);
	}

	/**
	 * Get the time when the token starts to be valid.
	 *
	 * @return int
	 */
	public function getNotBeforeTime() {
		return $this->getClaim(self::CLAIM_NOT_BEFORE);
	}

	/**
	 * Get the time when the token was issued.
	 *
	 * @return int
	 */
	public function getIssuedAtTime() {
		return $this->getClaim(self::CLAIM_ISSUED_AT);
	}

	/**
	 * Get issuer of the token.
	 *
	 * @return null|string
	 */
	public function getIssuer() {
		return $this->getClaim(self::CLAIM_ISSUER);
	}

	/**
	 * Get the (plugin) instance id for which the token was issued.
	 *
	 * The id will always be present.
	 *
	 * @return string
	 */
	public function getInstanceId() {
		return $this->getClaim(self::CLAIM_INSTANCE_ID);
	}

	/**
	 * Get the (plugin) instance name for which the token was issued.
	 *
	 * @return null|string
	 */
	public function getInstanceName() {
		return $this->getClaim(self::CLAIM_INSTANCE_NAME);
	}

	/**
	 * Get the id of the authenticated user.
	 *
	 * @return null|string
	 */
	public function getUserId() {
		return $this->getClaim(self::CLAIM_USER_ID);
	}

	/**
	 * Get the id of the user in an external system.
	 *
	 * Example use case would be to map user from an external store
	 * to the entry defined in the token.
	 *
	 * @return null|string
	 */
	public function getUserExternalId() {
		return $this->getClaim(self::CLAIM_USER_EXTERNAL_ID);
	}

	/**
	 * Get either the combined name of the user or the name of the token.
	 *
	 * @return null|string
	 */
	public function getFullName() {
		return $this->getClaim(self::CLAIM_USER_FULL_NAME);
	}

	/**
	 * Get the first name of the user accessing.
	 *
	 * @return null|string
	 */
	public function getFirstName() {
		return $this->getClaim(self::CLAIM_USER_FIRST_NAME);
	}

	/**
	 * Get the last name of the user accessing.
	 *
	 * @return null|string
	 */
	public function getLastName() {
		return $this->getClaim(self::CLAIM_USER_LAST_NAME);
	}

	/**
	 * Get the role of the accessing user.
	 *
	 * If this is set to “editor”, the requesting user may manage the contents
	 * of the plugin instance, i.e. she has administration rights.
	 * The type of the accessing entity can be either a “user” or a “editor”.
	 *
	 * @return null|string
	 */
	public function getRole() {
		return $this->getClaim(self::CLAIM_USER_ROLE);
	}

	/**
	 * Get the type of the token.
	 *
	 * The type of the accessing entity can be either a “user” or a “token”.
	 *
	 * @return null|string
	 */
	public function getType() {
		return $this->getClaim(self::CLAIM_ENTITY_TYPE);
	}

	/**
	 * Get text color used in the overall theme for this audience.
	 *
	 * The color is represented as a CSS-HEX code.
	 *
	 * @return null|string
	 */
	public function getThemeTextColor() {
		return $this->getClaim(self::CAIM_THEME_TEXT_COLOR);
	}

	/**
	 * Get background color used in the overall theme for this audience.
	 *
	 * The color is represented as a CSS-HEX code.
	 *
	 * @return null|string
	 */
	public function getThemeBackgroundColor() {
		return $this->getClaim(self::CAIM_THEME_BACKGROUND_COLOR);
	}

	/**
	 * Get the locale of the requesting user in the format of language tags.
	 *
	 * @return null|string
	 */	
	public function getLocale() {
		return $this->getClaim(self::CAIM_USER_LOCALE);
	}

	/**
	 * Check if the user is an editor.
	 *
	 * The user will always have a user role to prevent a bug class
	 * on missing values. Only when the editor role is explicitly 
	 * provided the user will be marked as editor. 
	 *
	 * @return boolean
	 */
	public function isEditor() {
		return $this->getClaim(self::CLAIM_USER_ROLE) === self::USER_ROLE_EDITOR;
	}

	/**
	 * Get all data stored in the token.
	 *
	 * @return array
	 */
	public function getData() {

		$res = [];
		$claims = $this->token->getClaims();

		foreach($claims as $claim)
			$res[$claim->getName()] = $claim->getValue();

		return $res;
	}
}