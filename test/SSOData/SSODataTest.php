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

namespace Staffbase\plugins\test\SSOData;

use PHPUnit\Framework\TestCase;
use Staffbase\plugins\sdk\SSOData\SharedDataTrait;
use Staffbase\plugins\sdk\SSOData\SSODataTrait;
use Staffbase\plugins\test\SSOTestData;

class SSODataTest extends TestCase
{

    /**
     *
     * Test accessors deliver correct values.
     *
     * @covers \Staffbase\plugins\sdk\SSOData\SharedDataTrait::getAudience()
     * @covers \Staffbase\plugins\sdk\SSOData\SharedDataTrait::getExpireAtTime()
     * @covers \Staffbase\plugins\sdk\SSOData\SharedDataTrait::getNotBeforeTime()
     * @covers \Staffbase\plugins\sdk\SSOData\SharedDataTrait::getIssuedAtTime()
     * @covers \Staffbase\plugins\sdk\SSOData\SharedDataTrait::getId()
     * @covers \Staffbase\plugins\sdk\SSOData\SharedDataTrait::getIssuer()
     * @covers \Staffbase\plugins\sdk\SSOData\SharedDataTrait::getSubject()
     * @covers \Staffbase\plugins\sdk\SSOData\SharedDataTrait::getRole()
     * @covers \Staffbase\plugins\sdk\SSOData\SSODataTrait::getInstanceId()
     * @covers \Staffbase\plugins\sdk\SSOData\SSODataTrait::getInstanceName()
     * @covers \Staffbase\plugins\sdk\SSOData\SSODataTrait::getUserId()
     * @covers \Staffbase\plugins\sdk\SSOData\SSODataTrait::getUserExternalId()
     * @covers \Staffbase\plugins\sdk\SSOData\SSODataTrait::getUserUsername()
     * @covers \Staffbase\plugins\sdk\SSOData\SSODataTrait::getUserPrimaryEmailAddress()
     * @covers \Staffbase\plugins\sdk\SSOData\SSODataTrait::getFullName()
     * @covers \Staffbase\plugins\sdk\SSOData\SSODataTrait::getFirstName()
     * @covers \Staffbase\plugins\sdk\SSOData\SSODataTrait::getLastName()
     * @covers \Staffbase\plugins\sdk\SSOData\SSODataTrait::getType()
     * @covers \Staffbase\plugins\sdk\SSOData\SSODataTrait::getThemeTextColor()
     * @covers \Staffbase\plugins\sdk\SSOData\SSODataTrait::getThemeBackgroundColor()
     * @covers \Staffbase\plugins\sdk\SSOData\SSODataTrait::getLocale()
     * @covers \Staffbase\plugins\sdk\SSOData\SSODataTrait::getTags()
     * @covers \Staffbase\plugins\sdk\SSOData\SSODataTrait::getBranchId()
     * @covers \Staffbase\plugins\sdk\SSOData\SSODataTrait::getTags()
     * @covers \Staffbase\plugins\sdk\SSOData\SSODataTrait::getSessionId()
     */
    public function testAccessorsGiveCorrectValues(): void
    {

        $tokenData = SSOTestData::getTokenData();
        $accessors = SSOTestData::getTokenAccessors();

        $ssoData = $this->getMockForTrait(SSODataTrait::class);

        $ssoData
            ->expects($this->exactly(count($accessors)))
            ->method('hasClaim')
            ->willReturnCallback(function ($key) use ($tokenData) {
                return isset($tokenData[$key]);
            });

        $ssoData
            ->expects($this->exactly(count($accessors)))
            ->method('getClaim')
            ->willReturnCallback(function ($key) use ($tokenData) {
                return $tokenData[$key];
            });

        foreach ($accessors as $key => $fn) {
            $this->assertEquals(
                $ssoData->$fn(),
                $tokenData[$key],
                "called $fn expected " .
                is_array($tokenData[$key]) ? print_r($tokenData[$key], true) : $tokenData[$key]
            );
        }
    }

    /**
     * Test isEditor return correct values.
     *
     * @covers \Staffbase\plugins\sdk\SSOToken::isEditor
     */
    public function testIsEditorReturnsCorrectValues(): void
    {

        $map = [
            null => false,
            '' => false,
            'use' => false,
            'edito' => false,
            'user' => false,
            'editor' => true,
        ];

        foreach ($map as $arg => $expect) {
            $tokenData = SSOTestData::getTokenData();
            $tokenData[SSOTestData::CLAIM_USER_ROLE] = $arg;

            $ssoData = $this->getMockForTrait(SSODataTrait::class);

            $ssoData
                ->method('hasClaim')
                ->willReturnCallback(function ($key) use ($tokenData) {
                    return isset($tokenData[$key]);
                });

            $ssoData
                ->method('getClaim')
                ->willReturnCallback(function ($key) use ($tokenData) {
                    return $tokenData[$key];
                });

            $this->assertEquals(
                $ssoData->isEditor(),
                $expect,
                "called isEditor on role [$arg] expected [$expect]"
            );
        }
    }

    /**
     * Test getData return correct values.
     *
     * @covers \Staffbase\plugins\sdk\SSOToken::getData
     */
    public function testGetDataReturnsCorrectValues(): void
    {

        $tokenData = SSOTestData::getTokenData();

        $ssoData = $this->getMockForTrait(SharedDataTrait::class);

        $ssoData
            ->method('getAllClaims')
            ->willReturnCallback(function () use ($tokenData) {
                return $tokenData;
            });

        $this->assertEquals($ssoData->getData(), $tokenData, "comparing data array to token");
    }
}
