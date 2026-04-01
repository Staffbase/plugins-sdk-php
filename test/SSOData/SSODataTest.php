<?php

declare(strict_types=1);
/**
 * SSO data Test implementation, based on this doc:
 * https://developers.staffbase.com/guide/customplugin-overview
 *
 * PHP version 7.4.0
 *
 * @category  Authentication
 * @copyright 2017-2022 Staffbase, GmbH.
 * @author    Vitaliy Ivanov
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 * @link      https://github.com/staffbase/plugins-sdk-php
 */

namespace Staffbase\plugins\test\SSOData;

use PHPUnit\Framework\TestCase;
use Staffbase\plugins\sdk\SSOData\SSODataTrait;
use Staffbase\plugins\test\SSOTestData;

class SSODataTest extends TestCase
{
    /**
     *
     * Test accessors deliver correct values.
     *
     */
    public function testAccessorsGiveCorrectValues(): void
    {

        $tokenData = SSOTestData::getTokenData();
        $accessors = SSOTestData::getTokenAccessors();

        $ssoData = new SSODataMock($tokenData);

        foreach ($accessors as $key => $fn) {
            $this->assertEquals(
                $tokenData[$key],
                $ssoData->$fn(),
                "called $fn expected " . print_r($tokenData[$key], true),
            );
        }
    }

    /**
     * Test isEditor return correct values.
     *
     */
    public function testIsEditorReturnsCorrectValues(): void
    {
        $map = [
            /** @phpstan-ignore array.invalidKey, array.duplicateKey */
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

            $ssoData = new SSODataMock($tokenData);
            $this->assertEquals(
                $ssoData->isEditor(),
                $expect,
                "called isEditor on role [$arg] expected [$expect]",
            );
        }
    }

    /**
     * Test getData return correct values.
     *
     */
    public function testGetDataReturnsCorrectValues(): void
    {

        $tokenData = SSOTestData::getTokenData();

        $ssoData = new SSODataMock($tokenData);


        $this->assertEquals($ssoData->getData(), $tokenData, "comparing data array to token");
    }
}

class SSODataMock
{
    use SSODataTrait;

    /**
     * @var array<string,mixed>
     */
    private array $claims;

    /**
     * @param array<string,mixed> $claims
     */
    public function __construct(array $claims = [])
    {
        $this->claims = $claims;
    }
    public function hasClaim(string $claim): bool
    {
        return isset($this->claims[$claim]);
    }
    public function getClaim(string $claim): mixed
    {
        return $this->claims[$claim];
    }

    /**
     * @return array<string,mixed>
     */
    public function getAllClaims(): array
    {
        return $this->claims;
    }
}
