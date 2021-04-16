<?php
/**
 * SSO token generator, based on this doc:
 * https://developers.staffbase.com/api/plugin-sso/
 *
 * PHP version 7.4.0
 *
 * @category  Authentication
 * @copyright 2017-2021 Staffbase, GmbH.
 * @author    Daniel Grosse
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 * @link      https://github.com/staffbase/plugins-sdk-php
 */

namespace Staffbase\plugins\sdk;


use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;

class SSOTokenGenerator
{
	/**
	 * Create a signed token from an array.
	 *
	 * Can be used in development in conjunction with getTokenData.
	 *
	 * @param string $privateKey private key
	 * @param array $tokenData associative array of claims
	 *
	 * @return string Encoded token.
	 */
	public static function createSignedTokenFromData($privateKey, $tokenData) {

		$config = Configuration::forSymmetricSigner(new Sha256(), InMemory::plainText($privateKey));

		$token = $config->builder()
			->issuedBy($tokenData[\Staffbase\plugins\sdk\SSOToken::CLAIM_ISSUER])
			->permittedFor($tokenData[SSOToken::CLAIM_AUDIENCE])
			->issuedAt($tokenData[SSOToken::CLAIM_ISSUED_AT])
			->canOnlyBeUsedAfter($tokenData[SSOToken::CLAIM_NOT_BEFORE])
			->expiresAt($tokenData[SSOToken::CLAIM_EXPIRE_AT])
			->relatedTo($tokenData[SSOToken::CLAIM_USER_ID])
			->withClaim(SSOToken::CLAIM_INSTANCE_ID, $tokenData[SSOToken::CLAIM_INSTANCE_ID])
			->withClaim(SSOToken::CLAIM_INSTANCE_NAME, $tokenData[SSOToken::CLAIM_INSTANCE_NAME])
			->withClaim(SSOToken::CLAIM_USER_EXTERNAL_ID, $tokenData[SSOToken::CLAIM_USER_EXTERNAL_ID])
			->withClaim(SSOToken::CLAIM_USER_USERNAME, $tokenData[SSOToken::CLAIM_USER_USERNAME])
			->withClaim(SSOToken::CLAIM_USER_PRIMARY_EMAIL_ADDRESS, $tokenData[SSOToken::CLAIM_USER_PRIMARY_EMAIL_ADDRESS])
			->withClaim(SSOToken::CLAIM_USER_FULL_NAME, $tokenData[SSOToken::CLAIM_USER_FULL_NAME])
			->withClaim(SSOToken::CLAIM_USER_FIRST_NAME, $tokenData[SSOToken::CLAIM_USER_FIRST_NAME])
			->withClaim(SSOToken::CLAIM_USER_LAST_NAME, $tokenData[SSOToken::CLAIM_USER_LAST_NAME])
			->withClaim(SSOToken::CLAIM_USER_ROLE, $tokenData[SSOToken::CLAIM_USER_ROLE])
			->withClaim(SSOToken::CLAIM_ENTITY_TYPE, $tokenData[SSOToken::CLAIM_ENTITY_TYPE])
			->withClaim(SSOToken::CLAIM_THEME_TEXT_COLOR, $tokenData[SSOToken::CLAIM_THEME_TEXT_COLOR])
			->withClaim(SSOToken::CLAIM_THEME_BACKGROUND_COLOR, $tokenData[SSOToken::CLAIM_THEME_BACKGROUND_COLOR])
			->withClaim(SSOToken::CLAIM_USER_LOCALE, $tokenData[SSOToken::CLAIM_USER_LOCALE])
			->withClaim(SSOToken::CLAIM_USER_TAGS, $tokenData[SSOToken::CLAIM_USER_TAGS])
			->withClaim(SSOToken::CLAIM_BRANCH_ID, $tokenData[SSOToken::CLAIM_BRANCH_ID])
			->withClaim(SSOToken::CLAIM_BRANCH_SLUG, $tokenData[SSOToken::CLAIM_BRANCH_SLUG])
			->withClaim(SSOToken::CLAIM_SESSION_ID, $tokenData[SSOToken::CLAIM_SESSION_ID])
			->getToken($config->signer(), $config->signingKey());

		return $token->toString();
	}

	/**
	 * Create an unsigned token by omitting sign().
	 *
	 * @param array $tokenData associative array of claims
	 *
	 * @return string Encoded token.
	 */
	public static function createUnsignedTokenFromData($tokenData) {

		$config = Configuration::forUnsecuredSigner();

		$token = $config->builder()
			->issuedBy($tokenData[SSOToken::CLAIM_ISSUER])
			->permittedFor($tokenData[SSOToken::CLAIM_AUDIENCE])
			->issuedAt($tokenData[SSOToken::CLAIM_ISSUED_AT])
			->canOnlyBeUsedAfter($tokenData[SSOToken::CLAIM_NOT_BEFORE])
			->expiresAt($tokenData[SSOToken::CLAIM_EXPIRE_AT])
			->relatedTo($tokenData[SSOToken::CLAIM_USER_ID])
			->withClaim(SSOToken::CLAIM_INSTANCE_ID, $tokenData[SSOToken::CLAIM_INSTANCE_ID])
			->withClaim(SSOToken::CLAIM_INSTANCE_NAME, $tokenData[SSOToken::CLAIM_INSTANCE_NAME])
			->withClaim(SSOToken::CLAIM_USER_EXTERNAL_ID, $tokenData[SSOToken::CLAIM_USER_EXTERNAL_ID])
			->withClaim(SSOToken::CLAIM_USER_USERNAME, $tokenData[SSOToken::CLAIM_USER_USERNAME])
			->withClaim(SSOToken::CLAIM_USER_PRIMARY_EMAIL_ADDRESS, $tokenData[SSOToken::CLAIM_USER_PRIMARY_EMAIL_ADDRESS])
			->withClaim(SSOToken::CLAIM_USER_FULL_NAME, $tokenData[SSOToken::CLAIM_USER_FULL_NAME])
			->withClaim(SSOToken::CLAIM_USER_FIRST_NAME, $tokenData[SSOToken::CLAIM_USER_FIRST_NAME])
			->withClaim(SSOToken::CLAIM_USER_LAST_NAME, $tokenData[SSOToken::CLAIM_USER_LAST_NAME])
			->withClaim(SSOToken::CLAIM_USER_ROLE, $tokenData[SSOToken::CLAIM_USER_ROLE])
			->withClaim(SSOToken::CLAIM_ENTITY_TYPE, $tokenData[SSOToken::CLAIM_ENTITY_TYPE])
			->withClaim(SSOToken::CLAIM_THEME_TEXT_COLOR, $tokenData[SSOToken::CLAIM_THEME_TEXT_COLOR])
			->withClaim(SSOToken::CLAIM_THEME_BACKGROUND_COLOR, $tokenData[SSOToken::CLAIM_THEME_BACKGROUND_COLOR])
			->withClaim(SSOToken::CLAIM_USER_LOCALE, $tokenData[SSOToken::CLAIM_USER_LOCALE])
			->withClaim(SSOToken::CLAIM_USER_TAGS, $tokenData[SSOToken::CLAIM_USER_TAGS])
			->withClaim(SSOToken::CLAIM_BRANCH_ID, $tokenData[SSOToken::CLAIM_BRANCH_ID])
			->withClaim(SSOToken::CLAIM_BRANCH_SLUG, $tokenData[SSOToken::CLAIM_BRANCH_SLUG])
			->withClaim(SSOToken::CLAIM_SESSION_ID, $tokenData[SSOToken::CLAIM_SESSION_ID])
			->getToken($config->signer(), $config->signingKey());

		return $token->toString();
	}
}
