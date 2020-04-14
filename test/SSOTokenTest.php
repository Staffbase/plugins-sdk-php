<?php
/**
 * SSO token Test implementation, based on this doc:
 * https://developers.staffbase.com/api/plugin-sso/
 *
 * PHP version 5.5.9
 *
 * @category  Authentication
 * @copyright 2017-2019 Staffbase, GmbH.
 * @author    Vitaliy Ivanov
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 * @link      https://github.com/staffbase/plugins-sdk-php
 */
namespace Staffbase\plugins\test;

use BadMethodCallException;
use Exception;
use ReflectionClass;
use phpseclib\Crypt\RSA;
use PHPUnit\Framework\TestCase;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Keychain;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Staffbase\plugins\sdk\Exceptions\SSOAuthenticationException;
use Staffbase\plugins\sdk\Exceptions\SSOException;
use Staffbase\plugins\sdk\SSOToken;

class SSOTokenTest extends TestCase
{
	private $publicKey;
	private $privateKey;
	private $classname = SSOToken::class;

	/**
	 * Constructor
	 *
	 * Creates an RSA-256 key pair.
     *
     * @return void
	 */
	public function setUp(): void {

		$rsa = new RSA();
		$keypair = $rsa->createKey();

		$this->publicKey  = $keypair['publickey'];
		$this->privateKey = $keypair['privatekey'];
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
			->set(SSOToken::CLAIM_BRANCH_ID, $tokenData[SSOToken::CLAIM_BRANCH_ID])
			->set(SSOToken::CLAIM_BRANCH_SLUG, $tokenData[SSOToken::CLAIM_BRANCH_SLUG])
            ->set(SSOToken::CLAIM_SESSION_ID, $tokenData[SSOToken::CLAIM_SESSION_ID])
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
			->set(SSOToken::CLAIM_BRANCH_ID, $tokenData[SSOToken::CLAIM_BRANCH_ID])
			->set(SSOToken::CLAIM_BRANCH_SLUG, $tokenData[SSOToken::CLAIM_BRANCH_SLUG])
            ->set(SSOToken::CLAIM_SESSION_ID, $tokenData[SSOToken::CLAIM_SESSION_ID])
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

        $this->expectException(SSOException::class);
        $this->expectExceptionMessage('Parameter appSecret for SSOToken is empty.');

        $reflectedClass = new ReflectionClass($this->classname);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke($mock, ' ', 'fake token');
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

        $this->expectException(SSOException::class);
        $this->expectExceptionMessage('Parameter tokenData for SSOToken is empty.');

        $reflectedClass = new ReflectionClass($this->classname);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke($mock, 'fake secret', ' ');
	}

    /**
     * @test
     *
     * Test constructor throws exception on empty token.
     *
     * @covers \Staffbase\plugins\sdk\SSOToken::__construct
     */
    public function testConstructorRefuseNonNumericLeeway() {

        $mock = $this->getMockBuilder($this->classname)
            ->disableOriginalConstructor()
            ->setMethods(array('parseToken'))
            ->getMock();

        $this->expectException(SSOException::class);
        $this->expectExceptionMessage('Parameter leeway has to be numeric.');

        $reflectedClass = new ReflectionClass($this->classname);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke($mock, 'fake secret', 'fake token', 'dd');
    }

	/**
	 * @test
	 *
	 * Test constructor throws exception on expired token.
	 *
	 * @covers \Staffbase\plugins\sdk\SSOToken::__construct
	 */
	public function testConstructorToFailOnExpiredToken() {

		$tokenData = SSODataTest::getTokenData();
		$tokenData[SSOToken::CLAIM_EXPIRE_AT] = strtotime("-1 minute");

		$token = self::createSignedTokenFromData($this->privateKey, $tokenData);

        $this->expectException(SSOAuthenticationException::class);

        new SSOToken($this->publicKey, $token);
    }

	/**
	 * @test
	 *
	 * Test constructor throws exception on a token valid in the future.
	 *
	 * @covers \Staffbase\plugins\sdk\SSOToken::__construct
	 */
	public function testConstructorToFailOnFutureToken() {

		$tokenData = SSODataTest::getTokenData();
		$tokenData[SSOToken::CLAIM_NOT_BEFORE] = strtotime("+1 minute");

		$token = self::createSignedTokenFromData($this->privateKey, $tokenData);

        $this->expectException(SSOAuthenticationException::class);

        new SSOToken($this->publicKey, $token);
	}

	/**
	 * @test
	 *
	 * Test constructor throws exception on a token issued in the future.
	 *
	 * @covers \Staffbase\plugins\sdk\SSOToken::__construct
	 */
	public function testConstructorToFailOnTokenIssuedInTheFuture() {

		$tokenData = SSODataTest::getTokenData();
		$tokenData[SSOToken::CLAIM_ISSUED_AT] = strtotime("+10 second");

		$token = self::createSignedTokenFromData($this->privateKey, $tokenData);

        $this->expectException(SSOAuthenticationException::class);

        new SSOToken($this->publicKey, $token);
	}

	/**
	 * @test
	 *
	 * Test constructor accepts a token issued in the future, by providing a leeway.
	 *
	 * @covers \Staffbase\plugins\sdk\SSOToken::__construct
	 */
	public function testConstructorAcceptsLeewayForTokenIssuedInTheFuture() {

		$leeway = 11;
		$tokenData = SSODataTest::getTokenData();
		$tokenData[SSOToken::CLAIM_ISSUED_AT] = strtotime("+10 second");

		$token = self::createSignedTokenFromData($this->privateKey, $tokenData);

		$sso = new SSOToken($this->publicKey, $token, $leeway);

        $this->assertNotEmpty($sso);
	}

	/**
	 * @test
	 *
	 * Test constructor throws exception on a token missing instance id.
	 *
	 * @covers \Staffbase\plugins\sdk\SSOToken::__construct
	 */
	public function testConstructorToFailOnMissingInstanceId() {

		$tokenData = SSODataTest::getTokenData();
		$tokenData[SSOToken::CLAIM_INSTANCE_ID] = '';

		$token = self::createSignedTokenFromData($this->privateKey, $tokenData);

        $this->expectException(SSOAuthenticationException::class);
        $this->expectExceptionMessage('Token lacks instance id.');

        new SSOToken($this->publicKey, $token);
	}

	/**
	 * @test
	 *
	 * Test constructor throws exception on a unsigned token.
	 *
	 * @covers \Staffbase\plugins\sdk\SSOToken::__construct
	 */
	public function testConstructorToFailOnUnsignedToken() {

		$tokenData = SSODataTest::getTokenData();

		$token = self::createUnsignedTokenFromData($tokenData);

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('This token is not signed');

        new SSOToken($this->publicKey, $token);
	}

	/**
	 * @test
	 *
	 * Test accessors deliver correct values.
	 *
	 * @covers \Staffbase\plugins\sdk\SSOToken::__construct
	 * @covers \Staffbase\plugins\sdk\SSOToken::getAudience()
	 * @covers \Staffbase\plugins\sdk\SSOToken::getExpireAtTime()
	 * @covers \Staffbase\plugins\sdk\SSOToken::getNotBeforeTime()
	 * @covers \Staffbase\plugins\sdk\SSOToken::getIssuedAtTime()
	 * @covers \Staffbase\plugins\sdk\SSOToken::getIssuer()
	 * @covers \Staffbase\plugins\sdk\SSOToken::getInstanceId()
	 * @covers \Staffbase\plugins\sdk\SSOToken::getInstanceName()
	 * @covers \Staffbase\plugins\sdk\SSOToken::getUserId()
	 * @covers \Staffbase\plugins\sdk\SSOToken::getUserExternalId()
	 * @covers \Staffbase\plugins\sdk\SSOToken::getFullName()
	 * @covers \Staffbase\plugins\sdk\SSOToken::getFirstName()
	 * @covers \Staffbase\plugins\sdk\SSOToken::getLastName()
	 * @covers \Staffbase\plugins\sdk\SSOToken::getRole()
	 * @covers \Staffbase\plugins\sdk\SSOToken::getType()
	 * @covers \Staffbase\plugins\sdk\SSOToken::getThemeTextColor()
	 * @covers \Staffbase\plugins\sdk\SSOToken::getThemeBackgroundColor()
	 * @covers \Staffbase\plugins\sdk\SSOToken::getLocale()
	 * @covers \Staffbase\plugins\sdk\SSOToken::getTags()
	 * @covers \Staffbase\plugins\sdk\SSOToken::hasClaim()
	 * @covers \Staffbase\plugins\sdk\SSOToken::getClaim()
	 * @covers \Staffbase\plugins\sdk\SSOToken::getBranchId()
	 * @covers \Staffbase\plugins\sdk\SSOToken::getBranchSlug()
     * @covers \Staffbase\plugins\sdk\SSOData::getSessionId()
	 */
	public function testAccessorsGiveCorrectValues() {

		$tokenData = SSODataTest::getTokenData();
		$accessors = SSODataTest::getTokenAccesors();

		$token = self::createSignedTokenFromData($this->privateKey, $tokenData);
		$ssoToken = new SSOToken($this->publicKey, $token);

		foreach ($accessors as $key => $fn) {
			$this->assertEquals(
				call_user_func([$ssoToken,$fn]),
				$tokenData[$key],
				"called $fn expected ".
				is_array($tokenData[$key]) ? print_r($tokenData[$key], true) : $tokenData[$key]);

		}
	}
}
