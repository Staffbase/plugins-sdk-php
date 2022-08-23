<?php

namespace Staffbase\plugins\test;

use DateTimeImmutable;

class SSOTestData {
	private const CLAIM_AUDIENCE                    = 'aud';
	private const CLAIM_EXPIRE_AT                   = 'exp';
	private const CLAIM_JWT_ID                 	    = 'jti';
	private const CLAIM_ISSUED_AT                   = 'iat';
	private const CLAIM_ISSUER                      = 'iss';
	private const CLAIM_NOT_BEFORE                  = 'nbf';
	private const CLAIM_SUBJECT                     = 'sub';

	public const CLAIM_USER_ROLE                   = 'role';
	private const CLAIM_SESSION_ID                  = 'sid';
	private const CLAIM_INSTANCE_ID                 = 'instance_id';
	private const CLAIM_INSTANCE_NAME               = 'instance_name';
	private const CLAIM_BRANCH_ID                   = 'branch_id';
	private const CLAIM_BRANCH_SLUG                 = 'branch_slug';
	private const CLAIM_USER_EXTERNAL_ID            = 'external_id';
	private const CLAIM_USER_USERNAME               = 'username';
	private const CLAIM_USER_PRIMARY_EMAIL_ADDRESS  = 'primary_email_address';
	private const CLAIM_USER_FULL_NAME              = 'name';
	private const CLAIM_USER_FIRST_NAME             = 'given_name';
	private const CLAIM_USER_LAST_NAME              = 'family_name';
	private const CLAIM_ENTITY_TYPE                 = 'type';
	private const CLAIM_THEME_TEXT_COLOR            = 'theming_text';
	private const CLAIM_THEME_BACKGROUND_COLOR      = 'theming_bg';
	private const CLAIM_USER_LOCALE                 = 'locale';
	private const CLAIM_USER_TAGS                   = 'tags';

	/**
	 * Create test data for a token.
	 *
	 * Can be used in development in conjunction with
	 * createSignedTokenFromData to issue development tokens.
	 *
	 * @param string|null $exp
	 * @param string|null $npf
	 * @param string|null $iat
	 * @return array Associative array of claims.
	 * @throws Exception
	 */
	public static function getTokenData(?string $exp = '10 minutes', ?string $npf = '-1 minutes', ?string $iat = 'now'): array
	{
		$exp = $exp ?? '10 minutes';
		$npf = $npf ?? '-1 minutes';
		$iat = $iat ?? 'now';

		$date = new DateTimeImmutable($iat);

		$tokenData = [];

		$tokenData[self::CLAIM_AUDIENCE] = 'testPlugin';
		$tokenData[self::CLAIM_EXPIRE_AT] = $date->modify($exp);
		$tokenData[self::CLAIM_NOT_BEFORE] = $date->modify($npf);
		$tokenData[self::CLAIM_ISSUED_AT] = $date;
		$tokenData[self::CLAIM_ISSUER] = 'api.staffbase.com';
		$tokenData[self::CLAIM_INSTANCE_ID] = '55c79b6ee4b06c6fb19bd1e2';
		$tokenData[self::CLAIM_INSTANCE_NAME] = 'Our locations';
		$tokenData[self::CLAIM_SUBJECT] = '541954c3e4b08bbdce1a340a';
		$tokenData[self::CLAIM_USER_EXTERNAL_ID] = 'jdoe';
		$tokenData[self::CLAIM_USER_USERNAME] = 'john.doe';
		$tokenData[self::CLAIM_USER_PRIMARY_EMAIL_ADDRESS] = 'jdoe@email.com';
		$tokenData[self::CLAIM_USER_FULL_NAME] = 'John Doe';
		$tokenData[self::CLAIM_USER_FIRST_NAME] = 'John';
		$tokenData[self::CLAIM_USER_LAST_NAME] = 'Doe';
		$tokenData[self::CLAIM_USER_ROLE] = 'editor';
		$tokenData[self::CLAIM_ENTITY_TYPE] = 'user';
		$tokenData[self::CLAIM_THEME_TEXT_COLOR] = '#00ABAB';
		$tokenData[self::CLAIM_THEME_BACKGROUND_COLOR] = '#FFAABB';
		$tokenData[self::CLAIM_USER_LOCALE] = 'en_US';
		$tokenData[self::CLAIM_USER_TAGS] = ['profile:field1:val', 'profile:field2:val'];
		$tokenData[self::CLAIM_BRANCH_ID] = "dev-id";
		$tokenData[self::CLAIM_BRANCH_SLUG] = "dev-slug";
		$tokenData[self::CLAIM_SESSION_ID] = "session-id";
		$tokenData[self::CLAIM_JWT_ID] = "jti-id";

		return $tokenData;
	}

	/**
	 * Get accessors map for supported tokens.
	 *
	 * @return array Associative array of claim accessors.
	 */
	public static function getTokenAccessors(): array
	{

		$accessors = [];

		$accessors[self::CLAIM_AUDIENCE] = 'getAudience';
		$accessors[self::CLAIM_EXPIRE_AT] = 'getExpireAtTime';
		$accessors[self::CLAIM_NOT_BEFORE] = 'getNotBeforeTime';
		$accessors[self::CLAIM_ISSUED_AT] = 'getIssuedAtTime';
		$accessors[self::CLAIM_ISSUER] = 'getIssuer';
		$accessors[self::CLAIM_INSTANCE_ID] = 'getInstanceId';
		$accessors[self::CLAIM_INSTANCE_NAME] = 'getInstanceName';
		$accessors[self::CLAIM_SUBJECT] = 'getUserId';
		$accessors[self::CLAIM_USER_EXTERNAL_ID] = 'getUserExternalId';
		$accessors[self::CLAIM_USER_USERNAME] = 'getUserUsername';
		$accessors[self::CLAIM_USER_PRIMARY_EMAIL_ADDRESS] = 'getUserPrimaryEmailAddress';
		$accessors[self::CLAIM_USER_FULL_NAME] = 'getFullName';
		$accessors[self::CLAIM_USER_FIRST_NAME] = 'getFirstName';
		$accessors[self::CLAIM_USER_LAST_NAME] = 'getLastName';
		$accessors[self::CLAIM_USER_ROLE] = 'getRole';
		$accessors[self::CLAIM_ENTITY_TYPE] = 'getType';
		$accessors[self::CLAIM_THEME_TEXT_COLOR] = 'getThemeTextColor';
		$accessors[self::CLAIM_THEME_BACKGROUND_COLOR] = 'getThemeBackgroundColor';
		$accessors[self::CLAIM_USER_LOCALE] = 'getLocale';
		$accessors[self::CLAIM_USER_TAGS] = 'getTags';
		$accessors[self::CLAIM_BRANCH_ID] = "getBranchId";
		$accessors[self::CLAIM_BRANCH_SLUG] = "getBranchSlug";
		$accessors[self::CLAIM_SESSION_ID] = 'getSessionId';
		$accessors[self::CLAIM_JWT_ID] = 'getId';

		return $accessors;
	}

}
