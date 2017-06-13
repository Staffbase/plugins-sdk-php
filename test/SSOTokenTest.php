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
use Staffbase\plugins\sdk\SSOData;
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
	 * Create a signed token from an array.
	 *
	 * Can be used in development in conjunction with getTokenData.
	 *
	 * @param string $privateKey private key
	 * @param array $tokenData associative array of claims
	 *
	 * @return string Encoded token.
	 */
	public static function createSignedTokenFromData($privateKey, $tokenData) {

		$signer   = new Sha256();
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
			->set(SSOToken::CLAIM_THEME_TEXT_COLOR, $tokenData[SSOToken::CLAIM_THEME_TEXT_COLOR])
			->set(SSOToken::CLAIM_THEME_BACKGROUND_COLOR, $tokenData[SSOToken::CLAIM_THEME_BACKGROUND_COLOR])
			->set(SSOToken::CLAIM_USER_LOCALE, $tokenData[SSOToken::CLAIM_USER_LOCALE])
			->set(SSOToken::CLAIM_USER_TAGS, $tokenData[SSOToken::CLAIM_USER_TAGS])
			->sign($signer, $keychain->getPrivateKey($privateKey))
			->getToken();

		return $token;
	}

	/**
	 * Create an unsigned token by omitting sign().
	 *
	 * @param array $tokenData associative array of claims
	 *
	 * @return string Encoded token.
	 */
	private static function createUnsignedTokenFromData($tokenData) {

		$signer   = new Sha256();
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
			->set(SSOToken::CLAIM_THEME_TEXT_COLOR, $tokenData[SSOToken::CLAIM_THEME_TEXT_COLOR])
			->set(SSOToken::CLAIM_THEME_BACKGROUND_COLOR, $tokenData[SSOToken::CLAIM_THEME_BACKGROUND_COLOR])
			->set(SSOToken::CLAIM_USER_LOCALE, $tokenData[SSOToken::CLAIM_USER_LOCALE])
			->set(SSOToken::CLAIM_USER_TAGS, $tokenData[SSOToken::CLAIM_USER_TAGS])
			->getToken();

		return $token;
	}

	/**
	 * @test
	 *
	 * Test constructor throws exception on empty secret.
	 *
	 * @covers \Staffbase\plugins\sdk\SSOToken::__construct
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
	 * Test constructor throws exception on empty token.
	 *
	 * @covers \Staffbase\plugins\sdk\SSOToken::__construct
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
	 * Test constructor throws exception on expired token.
	 * 
	 * @covers \Staffbase\plugins\sdk\SSOToken::__construct
	 */
	public function testConstructorToFailOnExpiredToken() {

		$tokendata = SSODataTest::getTokenData();
		$tokendata[SSOToken::CLAIM_EXPIRE_AT] = strtotime("-1 minute");

		$token = self::createSignedTokenFromData($this->privKey, $tokendata);

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
	 * Test constructor throws exception on a token valid in the future.
	 * 
	 * @covers \Staffbase\plugins\sdk\SSOToken::__construct
	 */
	public function testConstructorToFailOnFutureToken() {

		$tokendata = SSODataTest::getTokenData();
		$tokendata[SSOToken::CLAIM_NOT_BEFORE] = strtotime("+1 minute");

		$token = self::createSignedTokenFromData($this->privKey, $tokendata);

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
	 * Test constructor throws exception on a token missing instance id.
	 * 
	 * @covers \Staffbase\plugins\sdk\SSOToken::__construct
	 */
	public function testConstructorToFailOnMissingInstanceId() {

		$tokendata = SSODataTest::getTokenData();
		$tokendata[SSOToken::CLAIM_INSTANCE_ID] = '';

		$token = self::createSignedTokenFromData($this->privKey, $tokendata);

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
	 * Test constructor throws exception on a unsigned token.
	 * 
	 * @covers \Staffbase\plugins\sdk\SSOToken::__construct
	 */
	public function testConstructorToFailOnUnsignedToken() {

		$tokendata = SSODataTest::getTokenData();

		$token = self::createUnsignedTokenFromData($tokendata);

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
	 * @covers \Staffbase\plugins\std\SSOToken::getTags()
	 * @covers \Staffbase\plugins\std\SSOToken::hasClaim()
	 * @covers \Staffbase\plugins\std\SSOToken::getClaim()
	 */
	public function testAccesorsGiveCorrectValues() {

		$tokendata = SSODataTest::getTokenData();
		$accessors = SSODataTest::getTokenAccesors();

		$token = self::createSignedTokenFromData($this->privKey, $tokendata);
		$ssotoken = new SSOToken($this->pubKey, $token);


		foreach ($accessors as $key => $fn) {
			$this->assertEquals(
				call_user_func([$ssotoken,$fn]),
				$tokendata[$key],
				"called $fn expected ". $tokendata[$key]
			);
		}
	}
}
