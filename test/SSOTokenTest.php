<?php
/**
 * SSO token Test implementation, based on this doc:
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

use Exception;
use ReflectionClass;
use phpseclib\Crypt\RSA;
use PHPUnit\Framework\TestCase;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Keychain;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Staffbase\plugins\sdk\SSOToken;


class SSOTokenTest extends TestCase {
	
	private $pubKey;
	private $privKey;
	private $classname = SSOToken::class;

	/**
	 * Constructor
	 *
	 * Creates an RSA-256 key pair.
	 */
	public function __construct() {

		$rsa = new RSA();
		$keypair = $rsa->createKey();

		$this->pubKey  = $keypair['publickey'];
		$this->privKey = $keypair['privatekey'];
	}

	/**
	 * Create test data for a token.
	 *
	 * Can be used in development in conjunction with
	 * createSignedTokenFromData to issue development tokens.
	 *
	 * @return array Associative array of claims.
	 */
	protected function getTokenData() {

		$tokenData = [];

		$tokenData[SSOToken::CLAIM_AUDIENCE]              = 'testPlugin';
		$tokenData[SSOToken::CLAIM_EXPIRE_AT]             = strtotime('10 minutes');
		$tokenData[SSOToken::CLAIM_NOT_BEFORE]            = strtotime('-1 minute');
		$tokenData[SSOToken::CLAIM_ISSUED_AT]             = time();
		$tokenData[SSOToken::CLAIM_ISSUER]                = 'api.staffbase.com';
		$tokenData[SSOToken::CLAIM_INSTANCE_ID]           = '55c79b6ee4b06c6fb19bd1e2';
		$tokenData[SSOToken::CLAIM_INSTANCE_NAME]         = 'Our locations';
		$tokenData[SSOToken::CLAIM_USER_ID]               = '541954c3e4b08bbdce1a340a';
		$tokenData[SSOToken::CLAIM_USER_EXTERNAL_ID]      = 'jdoe';
		$tokenData[SSOToken::CLAIM_USER_FULL_NAME]        = 'John Doe';
		$tokenData[SSOToken::CLAIM_USER_FIRST_NAME]       = 'John';
		$tokenData[SSOToken::CLAIM_USER_LAST_NAME]        = 'Doe';
		$tokenData[SSOToken::CLAIM_USER_ROLE]             = 'editor';
		$tokenData[SSOToken::CLAIM_ENTITY_TYPE]           = 'user';
		$tokenData[SSOToken::CAIM_THEME_TEXT_COLOR]       = '#00ABAB';
		$tokenData[SSOToken::CAIM_THEME_BACKGROUND_COLOR] = '#FFAABB';
		$tokenData[SSOToken::CAIM_USER_LOCALE]            = 'en-US';

		return $tokenData;
	}

	/**
	 * Get accessors map for supported tokens.
	 *
	 * @return array Associative array of claim accessors.
	 */
	private function getTokenAccesors() {

		$acccessors = [];

		$acccessors[SSOToken::CLAIM_AUDIENCE]              = 'getAudience';
		$acccessors[SSOToken::CLAIM_EXPIRE_AT]             = 'getExpireAtTime';
		$acccessors[SSOToken::CLAIM_NOT_BEFORE]            = 'getNotBeforeTime';
		$acccessors[SSOToken::CLAIM_ISSUED_AT]             = 'getIssuedAtTime';
		$acccessors[SSOToken::CLAIM_ISSUER]                = 'getIssuer';
		$acccessors[SSOToken::CLAIM_INSTANCE_ID]           = 'getInstanceId';
		$acccessors[SSOToken::CLAIM_INSTANCE_NAME]         = 'getInstanceName';
		$acccessors[SSOToken::CLAIM_USER_ID]               = 'getUserId';
		$acccessors[SSOToken::CLAIM_USER_EXTERNAL_ID]      = 'getUserExternalId';
		$acccessors[SSOToken::CLAIM_USER_FULL_NAME]        = 'getFullName';
		$acccessors[SSOToken::CLAIM_USER_FIRST_NAME]       = 'getFirstName';
		$acccessors[SSOToken::CLAIM_USER_LAST_NAME]        = 'getLastName';
		$acccessors[SSOToken::CLAIM_USER_ROLE]             = 'getRole';
		$acccessors[SSOToken::CLAIM_ENTITY_TYPE]           = 'getType';
		$acccessors[SSOToken::CAIM_THEME_TEXT_COLOR]       = 'getThemeTextColor';
		$acccessors[SSOToken::CAIM_THEME_BACKGROUND_COLOR] = 'getThemeBackgroundColor';
		$acccessors[SSOToken::CAIM_USER_LOCALE]            = 'getLocale';

		return $acccessors;
	}

	/**
	 * Create a signed token from an array.
	 *
	 * Can be used in development in conjunction with getTokenData.
	 *
	 * @param array $tokenData Associative array of claims.
	 *
	 * @return string Encoded token.
	 */
	protected function createSignedTokenFromData($tokenData) {

		$signer = new Sha256();
		$keychain = new Keychain();

		$token = (new Builder())
			->setIssuer($tokenData[SSOToken::CLAIM_ISSUER])
			->setAudience($tokenData[SSOToken::CLAIM_AUDIENCE])
			->setIssuedAt($tokenData[SSOToken::CLAIM_ISSUED_AT])
			->setNotBefore($tokenData[SSOToken::CLAIM_NOT_BEFORE])
			->setExpiration($tokenData[SSOToken::CLAIM_EXPIRE_AT])
			->set(SSOToken::CLAIM_INSTANCE_ID, $tokenData[SSOToken::CLAIM_INSTANCE_ID])
			->set(SSOToken::CLAIM_INSTANCE_NAME, $tokenData[SSOToken::CLAIM_INSTANCE_NAME])
			->set(SSOToken::CLAIM_USER_ID, $tokenData[SSOToken::CLAIM_USER_ID])
			->set(SSOToken::CLAIM_USER_EXTERNAL_ID, $tokenData[SSOToken::CLAIM_USER_EXTERNAL_ID])
			->set(SSOToken::CLAIM_USER_FULL_NAME, $tokenData[SSOToken::CLAIM_USER_FULL_NAME])
			->set(SSOToken::CLAIM_USER_FIRST_NAME, $tokenData[SSOToken::CLAIM_USER_FIRST_NAME])
			->set(SSOToken::CLAIM_USER_LAST_NAME, $tokenData[SSOToken::CLAIM_USER_LAST_NAME])
			->set(SSOToken::CLAIM_USER_ROLE, $tokenData[SSOToken::CLAIM_USER_ROLE])
			->set(SSOToken::CLAIM_ENTITY_TYPE, $tokenData[SSOToken::CLAIM_ENTITY_TYPE])
			->set(SSOToken::CAIM_THEME_TEXT_COLOR, $tokenData[SSOToken::CAIM_THEME_TEXT_COLOR])
			->set(SSOToken::CAIM_THEME_BACKGROUND_COLOR, $tokenData[SSOToken::CAIM_THEME_BACKGROUND_COLOR])
			->set(SSOToken::CAIM_USER_LOCALE, $tokenData[SSOToken::CAIM_USER_LOCALE])
			->sign($signer, $keychain->getPrivateKey($this->privKey))
			->getToken();

		return $token;
	}

	/**
	 * Create an unsigned token by omitting sign().
	 *
	 * @param array $tokenData Associative array of claims.
	 *
	 * @return string Encoded token.
	 */
	private function createUnsignedTokenFromData($tokenData) {

		$signer = new Sha256();
		$keychain = new Keychain();

		$token = (new Builder())
			->setIssuer($tokenData[SSOToken::CLAIM_ISSUER])
			->setAudience($tokenData[SSOToken::CLAIM_AUDIENCE])
			->setIssuedAt($tokenData[SSOToken::CLAIM_ISSUED_AT])
			->setNotBefore($tokenData[SSOToken::CLAIM_NOT_BEFORE])
			->setExpiration($tokenData[SSOToken::CLAIM_EXPIRE_AT])
			->set(SSOToken::CLAIM_INSTANCE_ID, $tokenData[SSOToken::CLAIM_INSTANCE_ID])
			->set(SSOToken::CLAIM_INSTANCE_NAME, $tokenData[SSOToken::CLAIM_INSTANCE_NAME])
			->set(SSOToken::CLAIM_USER_ID, $tokenData[SSOToken::CLAIM_USER_ID])
			->set(SSOToken::CLAIM_USER_EXTERNAL_ID, $tokenData[SSOToken::CLAIM_USER_EXTERNAL_ID])
			->set(SSOToken::CLAIM_USER_FULL_NAME, $tokenData[SSOToken::CLAIM_USER_FULL_NAME])
			->set(SSOToken::CLAIM_USER_FIRST_NAME, $tokenData[SSOToken::CLAIM_USER_FIRST_NAME])
			->set(SSOToken::CLAIM_USER_LAST_NAME, $tokenData[SSOToken::CLAIM_USER_LAST_NAME])
			->set(SSOToken::CLAIM_USER_ROLE, $tokenData[SSOToken::CLAIM_USER_ROLE])
			->set(SSOToken::CLAIM_ENTITY_TYPE, $tokenData[SSOToken::CLAIM_ENTITY_TYPE])
			->set(SSOToken::CAIM_THEME_TEXT_COLOR, $tokenData[SSOToken::CAIM_THEME_TEXT_COLOR])
			->set(SSOToken::CAIM_THEME_BACKGROUND_COLOR, $tokenData[SSOToken::CAIM_THEME_BACKGROUND_COLOR])
			->set(SSOToken::CAIM_USER_LOCALE, $tokenData[SSOToken::CAIM_USER_LOCALE])
		//	->sign($signer, $keychain->getPrivateKey($this->privKey))
			->getToken();

		return $token;
	}

	/**
	 * @test
	 *
	 * Test Constructor throws Exception on empty secret.
	 *
	 * @covers \Staffbase\plugins\std\SSOToken::__construct
	 */
	public function testConstructorRefuseEmptySecret() {

		$mock = $this->getMockBuilder($this->classname)
			->disableOriginalConstructor()
			->setMethods(array('parseToken'))
			->getMock();

		try {

			$reflectedClass = new ReflectionClass($this->classname);
			$constructor = $reflectedClass->getConstructor();
			$constructor->invoke($mock, ' ', 'fake token');

		} catch (Exception $e) {

			return;
		}

		$this->fail();
	}

	/**
	 * @test
	 *
	 * Test Constructor throws Exception on empty token.
	 *
	 * @covers \Staffbase\plugins\std\SSOToken::__construct
	 */
	public function testConstructorRefuseEmptyToken() {

		$mock = $this->getMockBuilder($this->classname)
			->disableOriginalConstructor()
			->setMethods(array('parseToken'))
			->getMock();

		try {

			$reflectedClass = new ReflectionClass($this->classname);
			$constructor = $reflectedClass->getConstructor();
			$constructor->invoke($mock, 'fake secret', ' ');

		} catch (Exception $e) {

			return;
		}

		$this->fail();
	}

	/**
	 * @test
	 *
	 * Test accessors deliver correct values.
	 * 
	 * @covers \Staffbase\plugins\std\SSOToken::__construct
	 * @covers \Staffbase\plugins\std\SSOToken::getAudience()
	 * @covers \Staffbase\plugins\std\SSOToken::getExpireAtTime()
	 * @covers \Staffbase\plugins\std\SSOToken::getNotBeforeTime()
	 * @covers \Staffbase\plugins\std\SSOToken::getIssuedAtTime()
	 * @covers \Staffbase\plugins\std\SSOToken::getIssuer()
	 * @covers \Staffbase\plugins\std\SSOToken::getInstanceId()
	 * @covers \Staffbase\plugins\std\SSOToken::getInstanceName()
	 * @covers \Staffbase\plugins\std\SSOToken::getUserId()
	 * @covers \Staffbase\plugins\std\SSOToken::getUserExternalId()
	 * @covers \Staffbase\plugins\std\SSOToken::getFullName()
	 * @covers \Staffbase\plugins\std\SSOToken::getFirstName()
	 * @covers \Staffbase\plugins\std\SSOToken::getLastName()
	 * @covers \Staffbase\plugins\std\SSOToken::getRole()
	 * @covers \Staffbase\plugins\std\SSOToken::getType()
	 * @covers \Staffbase\plugins\std\SSOToken::getThemeTextColor()
	 * @covers \Staffbase\plugins\std\SSOToken::getThemeBackgroundColor()
	 * @covers \Staffbase\plugins\std\SSOToken::getLocale()
	 */
	public function testAccesorsGiveCorrectValues() {

		$tokendata = $this->getTokenData();
		$accessors = $this->getTokenAccesors();

		$token = $this->createSignedTokenFromData($tokendata);
		$ssotoken = new SSOToken($this->pubKey, $token);	

		foreach ($accessors as $key => $fn) {

			$this->assertEquals(
				call_user_func([$ssotoken,$fn]),
				$tokendata[$key], 
				"called $fn expected ". $tokendata[$key]);
		}
	}

	/**
	 * @test
	 *
	 * Test Constructor throws Exception on expired token.
	 * 
	 * @covers \Staffbase\plugins\std\SSOToken::__construct
	 */
	public function testConstructorToFailOnExpiredToken() {

		$tokendata = $this->getTokenData();

		$tokendata[SSOToken::CLAIM_EXPIRE_AT] = strtotime("-1 minute");

		$token = $this->createSignedTokenFromData($tokendata);

		try{ 

			$ssotoken = new SSOToken($this->pubKey, $token);

		} catch (Exception $e) {

			return;
		}
		$this->fail();
	}

	/**
	 * @test
	 *
	 * Test Constructor throws Exception on a token valid in the future.
	 * 
	 * @covers \Staffbase\plugins\std\SSOToken::__construct
	 */
	public function testConstructorToFailOnFutureToken() {

		$tokendata = $this->getTokenData();

		$tokendata[SSOToken::CLAIM_NOT_BEFORE] = strtotime("+1 minute");

		$token = $this->createSignedTokenFromData($tokendata);

		try {

			$ssotoken = new SSOToken($this->pubKey, $token);

		} catch (Exception $e) {

			return;
		}

		$this->fail();
	}

	/**
	 * @test
	 *
	 * Test Constructor throws Exception on a token missing instance id.
	 * 
	 * @covers \Staffbase\plugins\std\SSOToken::__construct
	 */
	public function testConstructorToFailOnMissingInstanceId() {

		$tokendata = $this->getTokenData();

		$tokendata[SSOToken::CLAIM_INSTANCE_ID] = '';

		$token = $this->createSignedTokenFromData($tokendata);

		try {

			$ssotoken = new SSOToken($this->pubKey, $token);

		} catch (Exception $e) {

			return;
		}

		$this->fail();
	}

	/**
	 * @test
	 *
	 * Test Constructor throws Exception on a unsigned token.
	 * 
	 * @covers \Staffbase\plugins\std\SSOToken::__construct
	 */
	public function testConstructorToFailOnUnsignedToken() {

		$tokendata = $this->getTokenData();

		$token = $this->createUnsignedTokenFromData($tokendata);

		try {
			$ssotoken = new SSOToken($this->pubKey, $token);
		} catch (Exception $e) {
			return;
		}
		$this->fail();
	}

	/**
	 * @test
	 *
	 * Test isEditor return correct values.
	 * 
	 * @covers \Staffbase\plugins\std\SSOToken::idEditor
	 */
	public function testIsEditorReturnsCorrectValues() {

		$map = [
			null     => false,
			''       => false,
			'use'    => false,
			'edito'  => false,
			SSOToken::USER_ROLE_USER   => false,
			SSOToken::USER_ROLE_EDITOR => true,
		];

		foreach($map as $arg => $res) {

			$tokendata = $this->getTokenData();
			$tokendata[SSOToken::CLAIM_USER_ROLE] = $arg;

			$token = $this->createSignedTokenFromData($tokendata);
			$ssotoken = new SSOToken($this->pubKey, $token);

			$this->assertEquals(
				$ssotoken->isEditor(),
				$res,
				"called isEditor on role [$arg] expected [$res]");
		}
	}

	/**
	 * @test
	 *
	 * Test getData return correct values.
	 * 
	 * @covers \Staffbase\plugins\std\SSOToken::getData
	 */
	public function testGetDataReturnsCorrectValues() {

		$tokendata = $this->getTokenData();

		$token = $this->createSignedTokenFromData($tokendata);
		$ssotoken = new SSOToken($this->pubKey, $token);	

		$this->assertEquals(
			$ssotoken->getData(),
			$tokendata,
			"comparing data array to token", 0, 10, true);
	} 
}
