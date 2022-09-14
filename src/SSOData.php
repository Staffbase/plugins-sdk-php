<?php

namespace Staffbase\plugins\sdk;

use Staffbase\plugins\sdk\SSOData\SharedDataTrait;
use Staffbase\plugins\sdk\SSOData\SSODataTrait;

/**
 * @deprecated Please use \Staffbase\plugins\sdk\SSOData\SSOData
 */
abstract class SSOData
{
    use SSODataTrait, SharedDataTrait;

    /**
     * @deprecated Please use \Staffbase\plugins\sdk\SSOToken::CLAIM_AUDIENCE
     * @see \Staffbase\plugins\sdk\SSOToken::CLAIM_AUDIENCE
     */
    const CLAIM_AUDIENCE               = 'aud';

    /**
     * @deprecated Please use \Staffbase\plugins\sdk\SSOToken::CLAIM_EXPIRE_AT
     * @see \Staffbase\plugins\sdk\SSOToken::CLAIM_EXPIRE_AT
     */
    const CLAIM_EXPIRE_AT              = 'exp';

    /**
     * @deprecated Please use \Staffbase\plugins\sdk\SSOToken::CLAIM_NOT_BEFORE
     * @see \Staffbase\plugins\sdk\SSOToken::CLAIM_NOT_BEFORE
     */
    const CLAIM_NOT_BEFORE             = 'nbf';

    /**
     * @deprecated Please use \Staffbase\plugins\sdk\SSOToken::CLAIM_ISSUED_AT
     * @see \Staffbase\plugins\sdk\SSOToken::CLAIM_ISSUED_AT
     */
    const CLAIM_ISSUED_AT              = 'iat';

    /**
     * @deprecated Please use \Staffbase\plugins\sdk\SSOToken::CLAIM_ISSUER
     * @see \Staffbase\plugins\sdk\SSOToken::CLAIM_ISSUER
     */
    const CLAIM_ISSUER                 = 'iss';

    /**
     * @deprecated Please use \Staffbase\plugins\sdk\SSOToken::CLAIM_INSTANCE_ID
     * @see \Staffbase\plugins\sdk\SSOToken::CLAIM_INSTANCE_ID
     */
    const CLAIM_INSTANCE_ID            = 'instance_id';

    /**
     * @deprecated Please use \Staffbase\plugins\sdk\SSOToken::CLAIM_INSTANCE_NAME
     * @see \Staffbase\plugins\sdk\SSOToken::CLAIM_INSTANCE_NAME
     */
    const CLAIM_INSTANCE_NAME          = 'instance_name';

    /**
     * @deprecated Please use \Staffbase\plugins\sdk\SSOToken::CLAIM_BRANCH_ID
     * @see \Staffbase\plugins\sdk\SSOToken::CLAIM_BRANCH_ID
     */
    const CLAIM_BRANCH_ID              = 'branch_id';

    /**
     * @deprecated Please use \Staffbase\plugins\sdk\SSOToken::CLAIM_BRANCH_SLUG
     * @see \Staffbase\plugins\sdk\SSOToken::CLAIM_BRANCH_SLUG
     */
    const CLAIM_BRANCH_SLUG            = 'branch_slug';

    /**
     * @deprecated Please use \Staffbase\plugins\sdk\SSOToken::CLAIM_USER_ID
     * @see \Staffbase\plugins\sdk\SSOToken::CLAIM_USER_ID
     */
    const CLAIM_USER_ID                = 'sub';

    /**
     * @deprecated Please use \Staffbase\plugins\sdk\SSOToken::CLAIM_USER_EXTERNAL_ID
     * @see \Staffbase\plugins\sdk\SSOToken::CLAIM_USER_EXTERNAL_ID
     */
    const CLAIM_USER_EXTERNAL_ID       = 'external_id';

    /**
     * @deprecated Please use \Staffbase\plugins\sdk\SSOToken::CLAIM_AUDIENCE
     * @see \Staffbase\plugins\sdk\SSOToken::CLAIM_AUDIENCE
     */
    const CLAIM_USER_FULL_NAME         = 'name';

    /**
     * @deprecated Please use \Staffbase\plugins\sdk\SSOToken::$USER_FIRST_NAME
     * @see \Staffbase\plugins\sdk\SSOToken::$USER_FIRST_NAME
     */
    const CLAIM_USER_FIRST_NAME        = 'given_name';

    /**
     * @deprecated Please use \Staffbase\plugins\sdk\SSOToken::$USER_LAST_NAME
     * @see \Staffbase\plugins\sdk\SSOToken::$USER_LAST_NAME
     */
    const CLAIM_USER_LAST_NAME         = 'family_name';

    /**
     * @deprecated Please use \Staffbase\plugins\sdk\SSOToken::CLAIM_USER_ROLE
     * @see \Staffbase\plugins\sdk\SSOToken::CLAIM_USER_ROLE
     */
    const CLAIM_USER_ROLE              = 'role';

    /**
     * @deprecated Please use \Staffbase\plugins\sdk\SSOToken::CLAIM_ENTITY_TYPE
     * @see \Staffbase\plugins\sdk\SSOToken::CLAIM_ENTITY_TYPE
     */
    const CLAIM_ENTITY_TYPE            = 'type';

    /**
     * @deprecated Please use \Staffbase\plugins\sdk\SSOToken::CLAIM_THEME_TEXT_COLOR
     * @see \Staffbase\plugins\sdk\SSOToken::CLAIM_THEME_TEXT_COLOR
     */
    const CLAIM_THEME_TEXT_COLOR       = 'theming_text';

    /**
     * @deprecated Please use \Staffbase\plugins\sdk\SSOToken::CLAIM_THEME_BACKGROUND_COLOR
     * @see \Staffbase\plugins\sdk\SSOToken::CLAIM_THEME_BACKGROUND_COLOR
     */
    const CLAIM_THEME_BACKGROUND_COLOR = 'theming_bg';

    /**
     * @deprecated Please use \Staffbase\plugins\sdk\SSOToken::CLAIM_USER_LOCALE
     * @see \Staffbase\plugins\sdk\SSOToken::CLAIM_USER_LOCALE
     */
    const CLAIM_USER_LOCALE            = 'locale';

    /**
     * @deprecated Please use \Staffbase\plugins\sdk\SSOToken::CLAIM_USER_TAGS
     * @see \Staffbase\plugins\sdk\SSOToken::CLAIM_USER_TAGS
     */
    const CLAIM_USER_TAGS              = 'tags';

    /**
     * @deprecated Will be removed
     */
    const USER_ROLE_EDITOR = 'editor';

    /**
     * @deprecated Will be removed
     */
    const REMOTE_CALL_DELETE = 'delete';
}
