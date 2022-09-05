<?php
/**
 * SSO token Test implementation, based on this doc:
 * https://developers.staffbase.com/guide/customplugin-overview
 *
 * PHP version 5.5.9
 *
 * @category  Authentication
 * @copyright 2017-2021 Staffbase, GmbH.
 * @author    Vitaliy Ivanov
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 * @link      https://github.com/staffbase/plugins-sdk-php
 */
namespace Staffbase\plugins\test;

use DateTimeImmutable;
use phpseclib\Crypt\RSA;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Staffbase\plugins\sdk\Exceptions\SSOAuthenticationException;
use Staffbase\plugins\sdk\Exceptions\SSOException;
use Staffbase\plugins\sdk\HeaderToken;
use Staffbase\plugins\sdk\SSOTokenGenerator;

class HeaderTokenTest extends TestCase
{
    private $publicKey;
    private $privateKey;
    private $classname = HeaderToken::class;

    /**
     * Constructor
     *
     * Creates an RSA-256 key pair.
     *
     * @return void
     */
    public function setUp(): void
    {

        $rsa = new RSA();
        $keypair = $rsa->createKey(2048);

        $this->publicKey  = $keypair['publickey'];
        $this->privateKey = $keypair['privatekey'];
    }

    /**
     *
     * Test constructor throws exception on empty secret.
     *
     * @covers \Staffbase\plugins\sdk\HeaderToken::__construct
     */
    public function testConstructorRefuseEmptySecret()
    {

        $mock = $this->getMockBuilder($this->classname)
            ->disableOriginalConstructor()
            ->onlyMethods(array('parseToken'))
            ->getMock();

        $this->expectException(SSOException::class);
        $this->expectExceptionMessage('Parameter appSecret for SSOToken is empty.');

        $reflectedClass = new ReflectionClass($this->classname);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke($mock, ' ', 'fake token');
    }

    /**
     *
     * Test constructor throws exception on empty token.
     *
     * @covers \Staffbase\plugins\sdk\HeaderToken::__construct
     */
    public function testConstructorRefuseEmptyToken()
    {

        $mock = $this->getMockBuilder($this->classname)
            ->disableOriginalConstructor()
            ->onlyMethods(array('parseToken'))
            ->getMock();

        $this->expectException(SSOException::class);
        $this->expectExceptionMessage('Parameter tokenData for SSOToken is empty.');

        $reflectedClass = new ReflectionClass($this->classname);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke($mock, 'fake secret', ' ');
    }

    /**
     *
     * Test constructor throws exception on expired token.
     *
     * @covers \Staffbase\plugins\sdk\HeaderToken::__construct
     */
    public function testConstructorToFailOnExpiredToken()
    {

        $tokenData = HeaderTestData::getTokenData("-1 minute");

        $token = SSOTokenGenerator::createSignedTokenFromData($this->privateKey, $tokenData);

        $this->expectException(SSOAuthenticationException::class);

        new HeaderToken($this->publicKey, $token);
    }

    /**
     *
     * Test constructor throws exception on a token valid in the future.
     *
     * @covers \Staffbase\plugins\sdk\HeaderToken::__construct
     */
    public function testConstructorToFailOnFutureToken()
    {

        $tokenData = HeaderTestData::getTokenData(null, "+1 minute");

        $token = SSOTokenGenerator::createSignedTokenFromData($this->privateKey, $tokenData);

        $this->expectException(SSOAuthenticationException::class);

        new HeaderToken($this->publicKey, $token);
    }

    /**
     *
     * Test constructor throws exception on a token issued in the future.
     *
     * @covers \Staffbase\plugins\sdk\HeaderToken::__construct
     */
    public function testConstructorToFailOnTokenIssuedInTheFuture()
    {

        $tokenData = HeaderTestData::getTokenData(null, null, "+10 second");

        $token = SSOTokenGenerator::createSignedTokenFromData($this->privateKey, $tokenData);

        $this->expectException(SSOAuthenticationException::class);

        new HeaderToken($this->publicKey, $token);
    }

    /**
     *
     * Test constructor accepts a token issued in the future, by providing a leeway.
     *
     * @covers \Staffbase\plugins\sdk\HeaderToken::__construct
     */
    public function testConstructorAcceptsLeewayForTokenIssuedInTheFuture()
    {

        $leeway = 11;
        $tokenData = HeaderTestData::getTokenData(null, null, "+10 second");

        $token = SSOTokenGenerator::createSignedTokenFromData($this->privateKey, $tokenData);

        $sso = new HeaderToken($this->publicKey, $token, $leeway);

        $this->assertNotEmpty($sso);
    }

    /**
     *
     * Test constructor throws exception on a unsigned token.
     *
     * @covers \Staffbase\plugins\sdk\HeaderToken::__construct
     */
    public function testConstructorToFailOnUnsignedToken()
    {

        $tokenData = HeaderTestData::getTokenData();

        $token = SSOTokenGenerator::createUnsignedTokenFromData($tokenData);

        $this->expectException(SSOAuthenticationException::class);
        $this->expectExceptionMessageMatches('/Token signer mismatch/');

        new HeaderToken($this->publicKey, $token);
    }

    /**
     *
     * Test accessors deliver correct values.
     *
     * @covers \Staffbase\plugins\sdk\HeaderToken::__construct
     * @covers \Staffbase\plugins\sdk\HeaderToken::getAudience()
     * @covers \Staffbase\plugins\sdk\HeaderToken::getExpireAtTime()
     * @covers \Staffbase\plugins\sdk\HeaderToken::getNotBeforeTime()
     * @covers \Staffbase\plugins\sdk\HeaderToken::getIssuedAtTime()
     * @covers \Staffbase\plugins\sdk\HeaderToken::getIssuer()
     * @covers \Staffbase\plugins\sdk\HeaderToken::getId()
     * @covers \Staffbase\plugins\sdk\HeaderToken::getUserId()
     * @covers \Staffbase\plugins\sdk\HeaderToken::getRole()
     * @covers \Staffbase\plugins\sdk\HeaderToken::hasClaim()
     * @covers \Staffbase\plugins\sdk\HeaderToken::getClaim()
     * @covers \Staffbase\plugins\sdk\HeaderToken::getBranchId()
     * @covers \Staffbase\plugins\sdk\HeaderToken::getUserId()
     * @covers \Staffbase\plugins\sdk\HeaderToken::getTokenId()
     */
    public function testAccessorsGiveCorrectValues()
    {

        $tokenData = HeaderTestData::getTokenData();
        $accessors = HeaderTestData::getTokenAccessors();

        $token = SSOTokenGenerator::createSignedTokenFromData($this->privateKey, $tokenData);
        $ssoToken = new HeaderToken($this->publicKey, $token);

        foreach ($accessors as $key => $fn) {
            $data = $tokenData[$key];

            if ($data instanceof DateTimeImmutable) {
                $data = $data->getTimestamp();
            }

            $data = is_array($data) ? print_r($data, true) : $data;

            $this->assertEquals(
                $tokenData[$key],
                $ssoToken->$fn(),
                "called $fn expected $data"
            );
        }
    }
}
