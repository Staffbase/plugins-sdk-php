<?php
/**
 * SSO data implementation, based on this doc:
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

/**
 * A container for the data transmitted from Staffbase app to a plugin
 * using the Staffbase single-sign-on.
 */
abstract class SSOData {
    const CLAIM_AUDIENCE               = 'aud';
    const CLAIM_EXPIRE_AT              = 'exp';
    const CLAIM_NOT_BEFORE             = 'nbf';
    const CLAIM_ISSUED_AT              = 'iat';
    const CLAIM_ISSUER                 = 'iss';
    const CLAIM_INSTANCE_ID            = 'instance_id';
    const CLAIM_INSTANCE_NAME          = 'instance_name';
    const CLAIM_USER_ID                = 'sub';
    const CLAIM_USER_EXTERNAL_ID       = 'external_id';
    const CLAIM_USER_FULL_NAME         = 'name';
    const CLAIM_USER_FIRST_NAME        = 'given_name';
    const CLAIM_USER_LAST_NAME         = 'family_name';
    const CLAIM_USER_ROLE              = 'role';
    const CLAIM_ENTITY_TYPE            = 'type';
    const CLAIM_THEME_TEXT_COLOR       = 'theming_text';
    const CLAIM_THEME_BACKGROUND_COLOR = 'theming_bg';
    const CLAIM_USER_LOCALE            = 'locale';
    const CLAIM_USER_TAGS              = 'tags';

    const USER_ROLE_EDITOR = 'editor';

    /**
     * Test if a claim is set.
     *
     * @param string $claim name.
     *
     * @return boolean
     */
    abstract protected function hasClaim($claim);

    /**
     * Get a claim without checking for existence.
     *
     * @param string $claim name.
     *
     * @return mixed
     */
    abstract protected function getClaim($claim);

    /**
     * Get an array of all available claims and their values.
     *
     * @return array
     */
    abstract protected function getAllClaims();

    /**
     * Internal getter for all token properties.
     *
     * Has a check for undefined claims to make getter calls always valid.
     *
     * @param string Name of the claim.
     *
     * @return mixed
     */
    protected function getClaimSafe($name) {

        if ($this->hasClaim($name)) 
            return $this->getClaim($name);

        return null;
    }

    /**
     * Get targeted audience of the token.
     *
     * @return null|string
     */
    public function getAudience() {
        return $this->getClaimSafe(self::CLAIM_AUDIENCE);
    }

    /**
     * Get the time when the token expires.
     *
     * @return int
     */
    public function getExpireAtTime() {
        return $this->getClaimSafe(self::CLAIM_EXPIRE_AT);
    }

    /**
     * Get the time when the token starts to be valid.
     *
     * @return int
     */
    public function getNotBeforeTime() {
        return $this->getClaimSafe(self::CLAIM_NOT_BEFORE);
    }

    /**
     * Get the time when the token was issued.
     *
     * @return int
     */
    public function getIssuedAtTime() {
        return $this->getClaimSafe(self::CLAIM_ISSUED_AT);
    }

    /**
     * Get issuer of the token.
     *
     * @return null|string
     */
    public function getIssuer() {
        return $this->getClaimSafe(self::CLAIM_ISSUER);
    }

    /**
     * Get the (plugin) instance id for which the token was issued.
     *
     * The id will always be present.
     *
     * @return string
     */
    public function getInstanceId() {
        return $this->getClaimSafe(self::CLAIM_INSTANCE_ID);
    }

    /**
     * Get the (plugin) instance name for which the token was issued.
     *
     * @return null|string
     */
    public function getInstanceName() {
        return $this->getClaimSafe(self::CLAIM_INSTANCE_NAME);
    }

    /**
     * Get the id of the authenticated user.
     *
     * @return null|string
     */
    public function getUserId() {
        return $this->getClaimSafe(self::CLAIM_USER_ID);
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
        return $this->getClaimSafe(self::CLAIM_USER_EXTERNAL_ID);
    }

    /**
     * Get either the combined name of the user or the name of the token.
     *
     * @return null|string
     */
    public function getFullName() {
        return $this->getClaimSafe(self::CLAIM_USER_FULL_NAME);
    }

    /**
     * Get the first name of the user accessing.
     *
     * @return null|string
     */
    public function getFirstName() {
        return $this->getClaimSafe(self::CLAIM_USER_FIRST_NAME);
    }

    /**
     * Get the last name of the user accessing.
     *
     * @return null|string
     */
    public function getLastName() {
        return $this->getClaimSafe(self::CLAIM_USER_LAST_NAME);
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
        return $this->getClaimSafe(self::CLAIM_USER_ROLE);
    }

    /**
     * Get the type of the token.
     *
     * The type of the accessing entity can be either a “user” or a “token”.
     *
     * @return null|string
     */
    public function getType() {
        return $this->getClaimSafe(self::CLAIM_ENTITY_TYPE);
    }

    /**
     * Get text color used in the overall theme for this audience.
     *
     * The color is represented as a CSS-HEX code.
     *
     * @return null|string
     */
    public function getThemeTextColor() {
        return $this->getClaimSafe(self::CLAIM_THEME_TEXT_COLOR);
    }

    /**
     * Get background color used in the overall theme for this audience.
     *
     * The color is represented as a CSS-HEX code.
     *
     * @return null|string
     */
    public function getThemeBackgroundColor() {
        return $this->getClaimSafe(self::CLAIM_THEME_BACKGROUND_COLOR);
    }

    /**
     * Get the locale of the requesting user in the format of language tags.
     *
     * @return string
     */
    public function getLocale() {
        return $this->getClaimSafe(self::CLAIM_USER_LOCALE);
    }

    /**
     * Get the user tags.
     *
     * @return array|null
     */
    public function getTags() {
        return $this->getClaimSafe(self::CLAIM_USER_TAGS);
    }

    /**
     * Check if the user is an editor.
     *
     * Only when the editor role is explicitly
     * provided the user will be marked as editor.
     *
     * @return boolean
     */
    public function isEditor() {
        return $this->getClaimSafe(self::CLAIM_USER_ROLE) === self::USER_ROLE_EDITOR;
    }

    /**
     * Get all stored data.
     *
     * @return array
     */
    public function getData() {
        return $this->getAllClaims();
    }   
}
