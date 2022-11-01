<?php
declare(strict_types=1);

/**
 * Interface with specific claims of a JWT token as url parameter.
 *
 * PHP version 7.4
 *
 * @category  Token
 * @copyright 2017-2022 Staffbase, GmbH.
 * @author    Daniel Grosse
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 * @link      https://github.com/staffbase/plugins-sdk-php
 */

namespace Staffbase\plugins\sdk\SSOData;

/**
 * Interface with specific claims of a JWT token as url parameter.
 */
interface SSODataClaimsInterface
{
    public const CLAIM_SESSION_ID = 'sid';
    public const CLAIM_INSTANCE_ID = 'instance_id';
    public const CLAIM_INSTANCE_NAME = 'instance_name';
    public const CLAIM_BRANCH_ID = 'branch_id';
    public const CLAIM_BRANCH_SLUG = 'branch_slug';
    public const CLAIM_USER_EXTERNAL_ID = 'external_id';
    public const CLAIM_USER_USERNAME = 'username';
    public const CLAIM_USER_PRIMARY_EMAIL_ADDRESS = 'primary_email_address';
    public const CLAIM_USER_FULL_NAME = 'name';
    public const CLAIM_USER_FIRST_NAME = 'given_name';
    public const CLAIM_USER_LAST_NAME = 'family_name';
    public const CLAIM_ENTITY_TYPE = 'type';
    public const CLAIM_THEME_TEXT_COLOR = 'theming_text';
    public const CLAIM_THEME_BACKGROUND_COLOR = 'theming_bg';
    public const CLAIM_USER_LOCALE = 'locale';
    public const CLAIM_USER_TAGS = 'tags';
    public const CLAIM_USER_ID = 'sub';
}
