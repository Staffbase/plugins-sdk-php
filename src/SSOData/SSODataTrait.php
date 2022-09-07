<?php
declare(strict_types=1);

/**
 * SSO data implementation, based on this doc:
 * https://developers.staffbase.com/guide/customplugin-overview
 *
 * PHP version 7.4
 *
 * @category  Authentication
 * @copyright 2017-2022 Staffbase, GmbH.
 * @author    Vitaliy Ivanov, Daniel Grosse
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 * @link      https://github.com/staffbase/plugins-sdk-php
 */

namespace Staffbase\plugins\sdk\SSOData;

/**
 * A trait for the data transmitted from Staffbase app to a plugin
 * using the Staffbase single-sign-on.
 */
trait SSODataTrait
{
    use SharedDataTrait, SSODataClaimsTrait, ClaimAccessTrait;

    /**
     * Get the branch id of the app that issued the token.
     *
     * The id will always be present.
     *
     * @return string
     */
    public function getBranchId(): string
    {
        return $this->getClaimSafe(self::$CLAIM_BRANCH_ID);
    }

    /**
     * Get the slug of the branch of the app that issued the token.
     *
     * @return null|string
     */
    public function getBranchSlug(): ?string
    {
        return $this->getClaimSafe(self::$CLAIM_BRANCH_SLUG);
    }

    /**
     * Get the cipher of the session id for the session the token was issued.
     *
     * The id will always be present.
     *
     * @return string
     */
    public function getSessionId(): string
    {
        return $this->getClaimSafe(self::$CLAIM_SESSION_ID);
    }

    /**
     * Get the (plugin) instance id for which the token was issued.
     *
     * The id will always be present.
     *
     * @return string
     */
    public function getInstanceId(): string
    {
        return $this->getClaimSafe(self::$CLAIM_INSTANCE_ID);
    }

    /**
     * Get the (plugin) instance name for which the token was issued.
     *
     * @return null|string
     */
    public function getInstanceName(): ?string
    {
        return $this->getClaimSafe(self::$CLAIM_INSTANCE_NAME);
    }

    /**
     * Get the id of the authenticated user.
     *
     * @return null|string
     */
    public function getUserId(): ?string
    {
        return $this->getClaimSafe(self::$CLAIM_USER_ID);
    }

    /**
     * Get the id of the user in an external system.
     *
     * Example use case would be to map user from an external store
     * to the entry defined in the token.
     *
     * @return null|string
     */
    public function getUserExternalId(): ?string
    {
        return $this->getClaimSafe(self::$CLAIM_USER_EXTERNAL_ID);
    }

    /**
     * Get the username of the user accessing.
     *
     * @return null|string
     */
    public function getUserUsername(): ?string
    {
        return $this->getClaimSafe(self::$CLAIM_USER_USERNAME);
    }

    /**
     * Get the primary email address of the user accessing.
     *
     * @return null|string
     */
    public function getUserPrimaryEmailAddress(): ?string
    {
        return $this->getClaimSafe(self::$CLAIM_USER_PRIMARY_EMAIL_ADDRESS);
    }

    /**
     * Get either the combined name of the user or the name of the token.
     *
     * @return null|string
     */
    public function getFullName(): ?string
    {
        return $this->getClaimSafe(self::$CLAIM_USER_FULL_NAME);
    }

    /**
     * Get the first name of the user accessing.
     *
     * @return null|string
     */
    public function getFirstName(): ?string
    {
        return $this->getClaimSafe(self::$CLAIM_USER_FIRST_NAME);
    }

    /**
     * Get the last name of the user accessing.
     *
     * @return null|string
     */
    public function getLastName(): ?string
    {
        return $this->getClaimSafe(self::$CLAIM_USER_LAST_NAME);
    }


    /**
     * Get the type of the token.
     *
     * The type of the accessing entity can be either a “user” or a “token”.
     *
     * @return null|string
     */
    public function getType(): ?string
    {
        return $this->getClaimSafe(self::$CLAIM_ENTITY_TYPE);
    }

    /**
     * Get text color used in the overall theme for this audience.
     *
     * The color is represented as a CSS-HEX code.
     *
     * @return null|string
     */
    public function getThemeTextColor(): ?string
    {
        return $this->getClaimSafe(self::$CLAIM_THEME_TEXT_COLOR);
    }

    /**
     * Get background color used in the overall theme for this audience.
     *
     * The color is represented as a CSS-HEX code.
     *
     * @return null|string
     */
    public function getThemeBackgroundColor(): ?string
    {
        return $this->getClaimSafe(self::$CLAIM_THEME_BACKGROUND_COLOR);
    }

    /**
     * Get the locale of the requesting user in the format of language tags.
     *
     * @return string
     */
    public function getLocale(): string
    {
        return $this->getClaimSafe(self::$CLAIM_USER_LOCALE);
    }

    /**
     * Get the user tags.
     *
     * @return array|null
     */
    public function getTags(): ?array
    {
        return $this->getClaimSafe(self::$CLAIM_USER_TAGS);
    }
}
