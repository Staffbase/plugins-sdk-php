<?php
/**
 * SSO data Test implementation, based on this doc:
 * https://developers.staffbase.com/guide/customplugin-overview
 *
 * PHP version 7.4.0
 *
 * @category  Authentication
 * @copyright 2017-2021 Staffbase, GmbH.
 * @author    Vitaliy Ivanov
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 * @link      https://github.com/staffbase/plugins-sdk-php
 */

namespace Staffbase\plugins\test;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Staffbase\plugins\sdk\SSOData;
use DateTimeImmutable;

class SSODataTest extends TestCase
{
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
     * @throws \Exception
     */
    public static function getTokenData(?string $exp = '10 minutes', ?string $npf = '-1 minutes', ?string $iat = 'now')
    {
        $exp = $exp ?? '10 minutes';
        $npf = $npf ?? '-1 minutes';
        $iat = $iat ?? 'now';

        $date = new DateTimeImmutable();

        $tokenData = [];

        $tokenData[SSOData::CLAIM_AUDIENCE] = 'testPlugin';
        $tokenData[SSOData::CLAIM_EXPIRE_AT] = $date->modify($exp);
        $tokenData[SSOData::CLAIM_NOT_BEFORE] = $date->modify($npf);
        $tokenData[SSOData::CLAIM_ISSUED_AT] = $date;
        $tokenData[SSOData::CLAIM_ISSUER] = 'api.staffbase.com';
        $tokenData[SSOData::CLAIM_INSTANCE_ID] = '55c79b6ee4b06c6fb19bd1e2';
        $tokenData[SSOData::CLAIM_INSTANCE_NAME] = 'Our locations';
        $tokenData[SSOData::CLAIM_USER_ID] = '541954c3e4b08bbdce1a340a';
        $tokenData[SSOData::CLAIM_USER_EXTERNAL_ID] = 'jdoe';
        $tokenData[SSOData::CLAIM_USER_USERNAME] = 'john.doe';
        $tokenData[SSOData::CLAIM_USER_PRIMARY_EMAIL_ADDRESS] = 'jdoe@email.com';
        $tokenData[SSOData::CLAIM_USER_FULL_NAME] = 'John Doe';
        $tokenData[SSOData::CLAIM_USER_FIRST_NAME] = 'John';
        $tokenData[SSOData::CLAIM_USER_LAST_NAME] = 'Doe';
        $tokenData[SSOData::CLAIM_USER_ROLE] = 'editor';
        $tokenData[SSOData::CLAIM_ENTITY_TYPE] = 'user';
        $tokenData[SSOData::CLAIM_THEME_TEXT_COLOR] = '#00ABAB';
        $tokenData[SSOData::CLAIM_THEME_BACKGROUND_COLOR] = '#FFAABB';
        $tokenData[SSOData::CLAIM_USER_LOCALE] = 'en_US';
        $tokenData[SSOData::CLAIM_USER_TAGS] = ['profile:field1:val', 'profile:field2:val'];
        $tokenData[SSOData::CLAIM_BRANCH_ID] = "dev-id";
        $tokenData[SSOData::CLAIM_BRANCH_SLUG] = "dev-slug";
        $tokenData[SSOData::CLAIM_SESSION_ID] = "session-id";

        return $tokenData;
    }

    /**
     * Get accessors map for supported tokens.
     *
     * @return array Associative array of claim accessors.
     */
    public static function getTokenAccessors()
    {

        $accessors = [];

        $accessors[SSOData::CLAIM_AUDIENCE] = 'getAudience';
        $accessors[SSOData::CLAIM_EXPIRE_AT] = 'getExpireAtTime';
        $accessors[SSOData::CLAIM_NOT_BEFORE] = 'getNotBeforeTime';
        $accessors[SSOData::CLAIM_ISSUED_AT] = 'getIssuedAtTime';
        $accessors[SSOData::CLAIM_ISSUER] = 'getIssuer';
        $accessors[SSOData::CLAIM_INSTANCE_ID] = 'getInstanceId';
        $accessors[SSOData::CLAIM_INSTANCE_NAME] = 'getInstanceName';
        $accessors[SSOData::CLAIM_USER_ID] = 'getUserId';
        $accessors[SSOData::CLAIM_USER_EXTERNAL_ID] = 'getUserExternalId';
        $accessors[SSOData::CLAIM_USER_USERNAME] = 'getUserUsername';
        $accessors[SSOData::CLAIM_USER_PRIMARY_EMAIL_ADDRESS] = 'getUserPrimaryEmailAddress';
        $accessors[SSOData::CLAIM_USER_FULL_NAME] = 'getFullName';
        $accessors[SSOData::CLAIM_USER_FIRST_NAME] = 'getFirstName';
        $accessors[SSOData::CLAIM_USER_LAST_NAME] = 'getLastName';
        $accessors[SSOData::CLAIM_USER_ROLE] = 'getRole';
        $accessors[SSOData::CLAIM_ENTITY_TYPE] = 'getType';
        $accessors[SSOData::CLAIM_THEME_TEXT_COLOR] = 'getThemeTextColor';
        $accessors[SSOData::CLAIM_THEME_BACKGROUND_COLOR] = 'getThemeBackgroundColor';
        $accessors[SSOData::CLAIM_USER_LOCALE] = 'getLocale';
        $accessors[SSOData::CLAIM_USER_TAGS] = 'getTags';
        $accessors[SSOData::CLAIM_BRANCH_ID] = "getBranchId";
        $accessors[SSOData::CLAIM_BRANCH_SLUG] = "getBranchSlug";
        $accessors[SSOData::CLAIM_SESSION_ID] = 'getSessionId';

        return $accessors;
    }

    /**
     * @test
     *
     * Test accessors deliver correct values.
     *
     * @covers \Staffbase\plugins\sdk\SSOData::getAudience()
     * @covers \Staffbase\plugins\sdk\SSOData::getExpireAtTime()
     * @covers \Staffbase\plugins\sdk\SSOData::getNotBeforeTime()
     * @covers \Staffbase\plugins\sdk\SSOData::getIssuedAtTime()
     * @covers \Staffbase\plugins\sdk\SSOData::getIssuer()
     * @covers \Staffbase\plugins\sdk\SSOData::getInstanceId()
     * @covers \Staffbase\plugins\sdk\SSOData::getInstanceName()
     * @covers \Staffbase\plugins\sdk\SSOData::getUserId()
     * @covers \Staffbase\plugins\sdk\SSOData::getUserExternalId()
     * @covers \Staffbase\plugins\sdk\SSOData::getUserUsername()
     * @covers \Staffbase\plugins\sdk\SSOData::getUserPrimaryEmailAddress()
     * @covers \Staffbase\plugins\sdk\SSOData::getFullName()
     * @covers \Staffbase\plugins\sdk\SSOData::getFirstName()
     * @covers \Staffbase\plugins\sdk\SSOData::getLastName()
     * @covers \Staffbase\plugins\sdk\SSOData::getRole()
     * @covers \Staffbase\plugins\sdk\SSOData::getType()
     * @covers \Staffbase\plugins\sdk\SSOData::getThemeTextColor()
     * @covers \Staffbase\plugins\sdk\SSOData::getThemeBackgroundColor()
     * @covers \Staffbase\plugins\sdk\SSOData::getLocale()
     * @covers \Staffbase\plugins\sdk\SSOData::getTags()
     * @covers \Staffbase\plugins\sdk\SSOData::getBranchId()
     * @covers \Staffbase\plugins\sdk\SSOData::getTags()
     * @covers \Staffbase\plugins\sdk\SSOData::getSessionId()
     */
    public function testAccessorsGiveCorrectValues()
    {

        $tokenData = self::getTokenData();
        $accessors = self::getTokenAccessors();

        $ssoData = $this->getMockForAbstractClass(SSOData::class);

        $ssoData
            ->expects($this->exactly(count($accessors)))
            ->method('hasClaim')
            ->will($this->returnCallback(function ($key) use ($tokenData) {
                return isset($tokenData[$key]);
            }));

        $ssoData
            ->expects($this->exactly(count($accessors)))
            ->method('getClaim')
            ->will($this->returnCallback(function ($key) use ($tokenData) {
                return $tokenData[$key];
            }));

        foreach ($accessors as $key => $fn) {
            $this->assertEquals(
                call_user_func([$ssoData, $fn]),
                $tokenData[$key],
                "called $fn expected " .
                is_array($tokenData[$key]) ? print_r($tokenData[$key], true) : $tokenData[$key]
            );
        }
    }

    /**
     * @test
     *
     * Test isEditor return correct values.
     *
     * @covers \Staffbase\plugins\sdk\SSOToken::isEditor
     */
    public function testIsEditorReturnsCorrectValues()
    {

        $map = [
            null => false,
            '' => false,
            'use' => false,
            'edito' => false,
            'user' => false,
            SSOData::USER_ROLE_EDITOR => true,
        ];

        foreach ($map as $arg => $expect) {
            $tokenData = self::getTokenData();
            $tokenData[SSOData::CLAIM_USER_ROLE] = $arg;

            $ssoData = $this->getMockForAbstractClass(SSOData::class);

            $ssoData
                ->method('hasClaim')
                ->will($this->returnCallback(function ($key) use ($tokenData) {
                    return isset($tokenData[$key]);
                }));

            $ssoData
                ->method('getClaim')
                ->will($this->returnCallback(function ($key) use ($tokenData) {
                    return $tokenData[$key];
                }));

            $this->assertEquals(
                $ssoData->isEditor(),
                $expect,
                "called isEditor on role [$arg] expected [$expect]"
            );
        }
    }

    /**
     * @test
     *
     * Test getData return correct values.
     *
     * @covers \Staffbase\plugins\sdk\SSOToken::getData
     */
    public function testGetDataReturnsCorrectValues()
    {

        $tokenData = self::getTokenData();

        $ssoData = $this->getMockForAbstractClass(SSOData::class);

        $ssoData
            ->method('getAllClaims')
            ->will($this->returnCallback(function () use ($tokenData) {
                return $tokenData;
            }));

        $this->assertEquals($ssoData->getData(), $tokenData, "comparing data array to token");
    }
}
