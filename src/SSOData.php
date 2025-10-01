<?php

declare(strict_types=1);

namespace Staffbase\plugins\sdk;

use Staffbase\plugins\sdk\SSOData\SharedDataTrait;
use Staffbase\plugins\sdk\SSOData\SSODataTrait;

/**
 * @deprecated Please use \Staffbase\plugins\sdk\SSOData\SSODataTrait
 */
abstract class SSOData
{
    use SSODataTrait;
    use SharedDataTrait;

    /**
     * @deprecated Please use \Staffbase\plugins\sdk\SSOToken::CLAIM_AUDIENCE
     * @see \Staffbase\plugins\sdk\SSOToken::CLAIM_AUDIENCE
     */
    public const CLAIM_AUDIENCE               = 'aud';

    /**
     * @deprecated Please use \Staffbase\plugins\sdk\SSOToken::CLAIM_EXPIRE_AT
     * @see \Staffbase\plugins\sdk\SSOToken::CLAIM_EXPIRE_AT
     */
    public const CLAIM_EXPIRE_AT              = 'exp';

    /**
     * @deprecated Please use \Staffbase\plugins\sdk\SSOToken::CLAIM_NOT_BEFORE
     * @see \Staffbase\plugins\sdk\SSOToken::CLAIM_NOT_BEFORE
     */
    public const CLAIM_NOT_BEFORE             = 'nbf';

    /**
     * @deprecated Please use \Staffbase\plugins\sdk\SSOToken::CLAIM_ISSUED_AT
     * @see \Staffbase\plugins\sdk\SSOToken::CLAIM_ISSUED_AT
     */
    public const CLAIM_ISSUED_AT              = 'iat';

    /**
     * @deprecated Please use \Staffbase\plugins\sdk\SSOToken::CLAIM_ISSUER
     * @see \Staffbase\plugins\sdk\SSOToken::CLAIM_ISSUER
     */
    public const CLAIM_ISSUER                 = 'iss';

    /**
     * @deprecated Please use \Staffbase\plugins\sdk\SSOToken::CLAIM_INSTANCE_ID
     * @see \Staffbase\plugins\sdk\SSOToken::CLAIM_INSTANCE_ID
     */
    public const CLAIM_INSTANCE_ID            = 'instance_id';

    /**
     * @deprecated Please use \Staffbase\plugins\sdk\SSOToken::CLAIM_INSTANCE_NAME
     * @see \Staffbase\plugins\sdk\SSOToken::CLAIM_INSTANCE_NAME
     */
    public const CLAIM_INSTANCE_NAME          = 'instance_name';

    /**
     * @deprecated Please use \Staffbase\plugins\sdk\SSOToken::CLAIM_BRANCH_ID
     * @see \Staffbase\plugins\sdk\SSOToken::CLAIM_BRANCH_ID
     */
    public const CLAIM_BRANCH_ID              = 'branch_id';

    /**
     * @deprecated Please use \Staffbase\plugins\sdk\SSOToken::CLAIM_BRANCH_SLUG
     * @see \Staffbase\plugins\sdk\SSOToken::CLAIM_BRANCH_SLUG
     */
    public const CLAIM_BRANCH_SLUG            = 'branch_slug';

    /**
     * @deprecated Please use \Staffbase\plugins\sdk\SSOToken::CLAIM_USER_ID
     * @see \Staffbase\plugins\sdk\SSOToken::CLAIM_USER_ID
     */
    public const CLAIM_USER_ID                = 'sub';

    /**
     * @deprecated Please use \Staffbase\plugins\sdk\SSOToken::CLAIM_USER_EXTERNAL_ID
     * @see \Staffbase\plugins\sdk\SSOToken::CLAIM_USER_EXTERNAL_ID
     */
    public const CLAIM_USER_EXTERNAL_ID       = 'external_id';

    /**
     * @deprecated Please use \Staffbase\plugins\sdk\SSOToken::CLAIM_AUDIENCE
     * @see \Staffbase\plugins\sdk\SSOToken::CLAIM_AUDIENCE
     */
    public const CLAIM_USER_FULL_NAME         = 'name';

    /**
     * @deprecated Please use \Staffbase\plugins\sdk\SSOToken::$USER_FIRST_NAME
     * @see \Staffbase\plugins\sdk\SSOToken::$USER_FIRST_NAME
     */
    public const CLAIM_USER_FIRST_NAME        = 'given_name';

    /**
     * @deprecated Please use \Staffbase\plugins\sdk\SSOToken::$USER_LAST_NAME
     * @see \Staffbase\plugins\sdk\SSOToken::$USER_LAST_NAME
     */
    public const CLAIM_USER_LAST_NAME         = 'family_name';

    /**
     * @deprecated Please use \Staffbase\plugins\sdk\SSOToken::CLAIM_USER_ROLE
     * @see \Staffbase\plugins\sdk\SSOToken::CLAIM_USER_ROLE
     */
    public const CLAIM_USER_ROLE              = 'role';

    /**
     * @deprecated Please use \Staffbase\plugins\sdk\SSOToken::CLAIM_ENTITY_TYPE
     * @see \Staffbase\plugins\sdk\SSOToken::CLAIM_ENTITY_TYPE
     */
    public const CLAIM_ENTITY_TYPE            = 'type';

    /**
     * @deprecated Please use \Staffbase\plugins\sdk\SSOToken::CLAIM_THEME_TEXT_COLOR
     * @see \Staffbase\plugins\sdk\SSOToken::CLAIM_THEME_TEXT_COLOR
     */
    public const CLAIM_THEME_TEXT_COLOR       = 'theming_text';

    /**
     * @deprecated Please use \Staffbase\plugins\sdk\SSOToken::CLAIM_THEME_BACKGROUND_COLOR
     * @see \Staffbase\plugins\sdk\SSOToken::CLAIM_THEME_BACKGROUND_COLOR
     */
    public const CLAIM_THEME_BACKGROUND_COLOR = 'theming_bg';

    /**
     * @deprecated Please use \Staffbase\plugins\sdk\SSOToken::CLAIM_USER_LOCALE
     * @see \Staffbase\plugins\sdk\SSOToken::CLAIM_USER_LOCALE
     */
    public const CLAIM_USER_LOCALE            = 'locale';

    /**
     * @deprecated Please use \Staffbase\plugins\sdk\SSOToken::CLAIM_USER_TAGS
     * @see \Staffbase\plugins\sdk\SSOToken::CLAIM_USER_TAGS
     */
    public const CLAIM_USER_TAGS              = 'tags';

    /**
     * @deprecated Will be removed
     */
    public const USER_ROLE_EDITOR = 'editor';

    /**
     * @deprecated Will be removed
     */
    public const REMOTE_CALL_DELETE = 'delete';
}
