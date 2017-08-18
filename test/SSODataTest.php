<?php
/**
 * SSO data Test implementation, based on this doc:
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
namespace Staffbase\plugins\test;

use PHPUnit_Framework_TestCase as TestCase;
use Staffbase\plugins\sdk\SSOData;

class SSODataTest extends TestCase {

	private $classname = SSOData::class;

	/**
	 * Create test data for a token.
	 *
	 * Can be used in development in conjunction with
	 * createSignedTokenFromData to issue development tokens.
	 *
	 * @return array Associative array of claims.
	 */
	public static function getTokenData() {

		$tokenData = [];

		$tokenData[SSOData::CLAIM_AUDIENCE]               = 'testPlugin';
		$tokenData[SSOData::CLAIM_EXPIRE_AT]              = strtotime('10 minutes');
		$tokenData[SSOData::CLAIM_NOT_BEFORE]             = strtotime('-1 minute');
		$tokenData[SSOData::CLAIM_ISSUED_AT]              = time();
		$tokenData[SSOData::CLAIM_ISSUER]                 = 'api.staffbase.com';
		$tokenData[SSOData::CLAIM_INSTANCE_ID]            = '55c79b6ee4b06c6fb19bd1e2';
		$tokenData[SSOData::CLAIM_INSTANCE_NAME]          = 'Our locations';
		$tokenData[SSOData::CLAIM_USER_ID]                = '541954c3e4b08bbdce1a340a';
		$tokenData[SSOData::CLAIM_USER_EXTERNAL_ID]       = 'jdoe';
		$tokenData[SSOData::CLAIM_USER_FULL_NAME]         = 'John Doe';
		$tokenData[SSOData::CLAIM_USER_FIRST_NAME]        = 'John';
		$tokenData[SSOData::CLAIM_USER_LAST_NAME]         = 'Doe';
		$tokenData[SSOData::CLAIM_USER_ROLE]              = 'editor';
		$tokenData[SSOData::CLAIM_ENTITY_TYPE]            = 'user';
		$tokenData[SSOData::CLAIM_THEME_TEXT_COLOR]       = '#00ABAB';
		$tokenData[SSOData::CLAIM_THEME_BACKGROUND_COLOR] = '#FFAABB';
		$tokenData[SSOData::CLAIM_USER_LOCALE]            = 'en_US';
		$tokenData[SSOData::CLAIM_USER_TAGS]              = ['profile:field1:val', 'profile:field2:val'];

		return $tokenData;
	}

	/**
	 * Get accessors map for supported tokens.
	 *
	 * @return array Associative array of claim accessors.
	 */
	public static function getTokenAccesors() {

		$acccessors = [];

		$acccessors[SSOData::CLAIM_AUDIENCE]               = 'getAudience';
		$acccessors[SSOData::CLAIM_EXPIRE_AT]              = 'getExpireAtTime';
		$acccessors[SSOData::CLAIM_NOT_BEFORE]             = 'getNotBeforeTime';
		$acccessors[SSOData::CLAIM_ISSUED_AT]              = 'getIssuedAtTime';
		$acccessors[SSOData::CLAIM_ISSUER]                 = 'getIssuer';
		$acccessors[SSOData::CLAIM_INSTANCE_ID]            = 'getInstanceId';
		$acccessors[SSOData::CLAIM_INSTANCE_NAME]          = 'getInstanceName';
		$acccessors[SSOData::CLAIM_USER_ID]                = 'getUserId';
		$acccessors[SSOData::CLAIM_USER_EXTERNAL_ID]       = 'getUserExternalId';
		$acccessors[SSOData::CLAIM_USER_FULL_NAME]         = 'getFullName';
		$acccessors[SSOData::CLAIM_USER_FIRST_NAME]        = 'getFirstName';
		$acccessors[SSOData::CLAIM_USER_LAST_NAME]         = 'getLastName';
		$acccessors[SSOData::CLAIM_USER_ROLE]              = 'getRole';
		$acccessors[SSOData::CLAIM_ENTITY_TYPE]            = 'getType';
		$acccessors[SSOData::CLAIM_THEME_TEXT_COLOR]       = 'getThemeTextColor';
		$acccessors[SSOData::CLAIM_THEME_BACKGROUND_COLOR] = 'getThemeBackgroundColor';
		$acccessors[SSOData::CLAIM_USER_LOCALE]            = 'getLocale';
		$acccessors[SSOData::CLAIM_USER_TAGS]              = 'getTags';

		return $acccessors;
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
	 * @covers \Staffbase\plugins\sdk\SSOData::getFullName()
	 * @covers \Staffbase\plugins\sdk\SSOData::getFirstName()
	 * @covers \Staffbase\plugins\sdk\SSOData::getLastName()
	 * @covers \Staffbase\plugins\sdk\SSOData::getRole()
	 * @covers \Staffbase\plugins\sdk\SSOData::getType()
	 * @covers \Staffbase\plugins\sdk\SSOData::getThemeTextColor()
	 * @covers \Staffbase\plugins\sdk\SSOData::getThemeBackgroundColor()
	 * @covers \Staffbase\plugins\sdk\SSOData::getLocale()
	 * @covers \Staffbase\plugins\sdk\SSOData::getTags()
	 */
	public function testAccesorsGiveCorrectValues() {

		$tokendata = self::getTokenData();
		$accessors = self::getTokenAccesors();

		$ssodata = $this->getMockForAbstractClass(SSOData::class);

		$ssodata
			->expects($this->exactly(count($accessors)))
			->method('hasClaim')
			->will($this->returnCallback(function ($key) use ($tokendata) {
				return isset($tokendata[$key]);
			}));

		$ssodata
			->expects($this->exactly(count($accessors)))
			->method('getClaim')
			->will($this->returnCallback(function ($key) use ($tokendata) {
				return $tokendata[$key];
			}));

		foreach ($accessors as $key => $fn) {

			$this->assertEquals(
				call_user_func([$ssodata,$fn]),
				$tokendata[$key], 
				"called $fn expected ". $tokendata[$key]);
		}
	}

	/**
	 * @test
	 *
	 * Test isEditor return correct values.
	 * 
	 * @covers \Staffbase\plugins\sdk\SSOToken::idEditor
	 */
	public function testIsEditorReturnsCorrectValues() {

		$map = [
			null                      => false,
			''                        => false,
			'use'                     => false,
			'edito'                   => false,
			'user'                    => false,
			SSOData::USER_ROLE_EDITOR => true,
		];

		foreach($map as $arg => $expect) {

			$tokendata = self::getTokenData();
			$tokendata[SSOData::CLAIM_USER_ROLE] = $arg;

			$ssodata = $this->getMockForAbstractClass(SSOData::class);

			$ssodata
				->method('hasClaim')
				->will($this->returnCallback(function ($key) use ($tokendata) {
					return isset($tokendata[$key]);
				}));

			$ssodata
				->method('getClaim')
				->will($this->returnCallback(function ($key) use ($tokendata) {
					return $tokendata[$key];
				}));

			$this->assertEquals(
				$ssodata->isEditor(),
				$expect,
				"called isEditor on role [$arg] expected [$res]");
		}
	}

	/**
	 * @test
	 *
	 * Test getData return correct values.
	 * 
	 * @covers \Staffbase\plugins\sdk\SSOToken::getData
	 */
	public function testGetDataReturnsCorrectValues() {

		$tokendata = self::getTokenData();
		$accessors = self::getTokenAccesors();

		$ssodata = $this->getMockForAbstractClass(SSOData::class);

		$ssodata
			->method('getAllClaims')
			->will($this->returnCallback(function () use ($tokendata) {
				return $tokendata;
			}));

		$this->assertEquals(
			$ssodata->getData(),
			$tokendata,
			"comparing data array to token", 0, 10, true);
	}

}
