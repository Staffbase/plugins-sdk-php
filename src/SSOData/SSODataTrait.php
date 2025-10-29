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
    use SharedDataTrait;

    /**
     * Get the branch id of the app that issued the token.
     *
     * @return null|string
     */
    public function getBranchId(): ?string
    {
        $value = $this->getClaimSafe(SSODataClaimsInterface::CLAIM_BRANCH_ID);
        return is_string($value) ? $value : null;
    }

    /**
     * Get the slug of the branch of the app that issued the token.
     *
     * @return null|string
     */
    public function getBranchSlug(): ?string
    {
        $value = $this->getClaimSafe(SSODataClaimsInterface::CLAIM_BRANCH_SLUG);
        return is_string($value) ? $value : null;
    }

    /**
     * Get the cipher of the session id for the session the token was issued.
     *
     * @return null|string
     */
    public function getSessionId(): ?string
    {
        $value = $this->getClaimSafe(SSODataClaimsInterface::CLAIM_SESSION_ID);
        return is_string($value) ? $value : null;
    }

    /**
     * Get the (plugin) instance id for which the token was issued.
     *
     * The id will always be present.
     *
     * @return null|string
     */
    public function getInstanceId(): ?string
    {
        $value = $this->getClaimSafe(SSODataClaimsInterface::CLAIM_INSTANCE_ID);
        return is_string($value) ? $value : null;
    }

    /**
     * Get the (plugin) instance name for which the token was issued.
     *
     * @return null|string
     */
    public function getInstanceName(): ?string
    {
        $value = $this->getClaimSafe(SSODataClaimsInterface::CLAIM_INSTANCE_NAME);
        return is_string($value) ? $value : null;
    }

    /**
     * Get the id of the authenticated user.
     *
     * @return null|string
     */
    public function getUserId(): ?string
    {
        $value = $this->getClaimSafe(SSODataClaimsInterface::CLAIM_USER_ID);
        return is_string($value) ? $value : null;
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
        $value = $this->getClaimSafe(SSODataClaimsInterface::CLAIM_USER_EXTERNAL_ID);
        return is_string($value) ? $value : null;
    }

    /**
     * Get the username of the user accessing.
     *
     * @return null|string
     */
    public function getUserUsername(): ?string
    {
        $value = $this->getClaimSafe(SSODataClaimsInterface::CLAIM_USER_USERNAME);
        return is_string($value) ? $value : null;
    }

    /**
     * Get the primary email address of the user accessing.
     *
     * @return null|string
     */
    public function getUserPrimaryEmailAddress(): ?string
    {
        $value = $this->getClaimSafe(SSODataClaimsInterface::CLAIM_USER_PRIMARY_EMAIL_ADDRESS);
        return is_string($value) ? $value : null;
    }

    /**
     * Get either the combined name of the user or the name of the token.
     *
     * @return null|string
     */
    public function getFullName(): ?string
    {
        $value = $this->getClaimSafe(SSODataClaimsInterface::CLAIM_USER_FULL_NAME);
        return is_string($value) ? $value : null;
    }

    /**
     * Get the first name of the user accessing.
     *
     * @return null|string
     */
    public function getFirstName(): ?string
    {
        $value = $this->getClaimSafe(SSODataClaimsInterface::CLAIM_USER_FIRST_NAME);
        return is_string($value) ? $value : null;
    }

    /**
     * Get the last name of the user accessing.
     *
     * @return null|string
     */
    public function getLastName(): ?string
    {
        $value = $this->getClaimSafe(SSODataClaimsInterface::CLAIM_USER_LAST_NAME);
        return is_string($value) ? $value : null;
    }


    /**
     * Get the type of the token.
     *
     * The type of the accessing entity can be either a "user" or a "token".
     *
     * @return null|string
     */
    public function getType(): ?string
    {
        $value = $this->getClaimSafe(SSODataClaimsInterface::CLAIM_ENTITY_TYPE);
        return is_string($value) ? $value : null;
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
        $value = $this->getClaimSafe(SSODataClaimsInterface::CLAIM_THEME_TEXT_COLOR);
        return is_string($value) ? $value : null;
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
        $value = $this->getClaimSafe(SSODataClaimsInterface::CLAIM_THEME_BACKGROUND_COLOR);
        return is_string($value) ? $value : null;
    }

    /**
     * Get the locale of the requesting user in the format of language tags.
     *
     * @return string
     */
    public function getLocale(): string
    {
        $val = $this->getClaimSafe(SSODataClaimsInterface::CLAIM_USER_LOCALE);
        return is_string($val) ? $val : '';
    }

    /**
     * Get the user tags.
     *
     * @return array<mixed>|null
     */
    public function getTags(): ?array
    {
        $val = $this->getClaimSafe(SSODataClaimsInterface::CLAIM_USER_TAGS);
        return is_array($val) ? $val : null;
    }
}
