<?php
/**
 * SSO token Test implementation, based on this doc:
 * https://developers.staffbase.com/guide/customplugin-overview
 *
 * PHP version 7.4
 *
 * @category  Authentication
 * @copyright 2017-2022 Staffbase, GmbH.
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
use Staffbase\plugins\sdk\SSOData\SSODataClaimsInterface;
use Staffbase\plugins\sdk\SSOToken;
use Staffbase\plugins\sdk\SSOTokenGenerator;

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
     * @covers \Staffbase\plugins\sdk\SSOToken::__construct
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
     * @covers \Staffbase\plugins\sdk\SSOToken::__construct
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
     * @covers \Staffbase\plugins\sdk\SSOToken::__construct
     */
    public function testConstructorToFailOnExpiredToken()
    {

        $tokenData = SSOTestData::getTokenData("-1 minute");

        $token = SSOTokenGenerator::createSignedTokenFromData($this->privateKey, $tokenData);

        $this->expectException(SSOAuthenticationException::class);

        new SSOToken($this->publicKey, $token);
    }

    /**
     *
     * Test constructor throws exception on a token valid in the future.
     *
     * @covers \Staffbase\plugins\sdk\SSOToken::__construct
     */
    public function testConstructorToFailOnFutureToken()
    {

        $tokenData = SSOTestData::getTokenData(null, "+1 minute");

        $token = SSOTokenGenerator::createSignedTokenFromData($this->privateKey, $tokenData);

        $this->expectException(SSOAuthenticationException::class);

        new SSOToken($this->publicKey, $token);
    }

    /**
     *
     * Test constructor throws exception on a token issued in the future.
     *
     * @covers \Staffbase\plugins\sdk\SSOToken::__construct
     */
    public function testConstructorToFailOnTokenIssuedInTheFuture()
    {

        $tokenData = SSOTestData::getTokenData(null, null, "+10 second");

        $token = SSOTokenGenerator::createSignedTokenFromData($this->privateKey, $tokenData);

        $this->expectException(SSOAuthenticationException::class);

        new SSOToken($this->publicKey, $token);
    }

    /**
     *
     * Test constructor accepts a token issued in the future, by providing a leeway.
     *
     * @covers \Staffbase\plugins\sdk\SSOToken::__construct
     */
    public function testConstructorAcceptsLeewayForTokenIssuedInTheFuture()
    {

        $leeway = 11;
        $tokenData = SSOTestData::getTokenData(null, null, "+10 second");

        $token = SSOTokenGenerator::createSignedTokenFromData($this->privateKey, $tokenData);

        $sso = new SSOToken($this->publicKey, $token, $leeway);

        $this->assertNotEmpty($sso);
    }

    /**
     *
     * Test constructor throws exception on a token missing instance id.
     *
     * @covers \Staffbase\plugins\sdk\SSOToken::__construct
     * @covers \Staffbase\plugins\sdk\Validation\HasInstanceId
     */
    public function testConstructorToFailOnMissingInstanceId()
    {

        $tokenData = SSOTestData::getTokenData();
        $tokenData[SSODataClaimsInterface::CLAIM_INSTANCE_ID] = '';

        $token = SSOTokenGenerator::createSignedTokenFromData($this->privateKey, $tokenData);

        $this->expectException(SSOAuthenticationException::class);
        $this->expectExceptionMessage('Token lacks instance id.');

        new SSOToken($this->publicKey, $token);
    }

    /**
     *
     * Test constructor throws exception on a unsigned token.
     *
     * @covers \Staffbase\plugins\sdk\SSOToken::__construct
     */
    public function testConstructorToFailOnUnsignedToken()
    {

        $tokenData = SSOTestData::getTokenData();

        $token = SSOTokenGenerator::createUnsignedTokenFromData($tokenData);

        $this->expectException(SSOAuthenticationException::class);
        $this->expectExceptionMessageMatches('/Token signer mismatch/');

        new SSOToken($this->publicKey, $token);
    }

    /**
     *
     * Test accessors deliver correct values.
     *
     * @covers \Staffbase\plugins\sdk\SSOToken::__construct
     * @covers \Staffbase\plugins\sdk\SSOToken::getAudience()
     * @covers \Staffbase\plugins\sdk\SSOToken::getExpireAtTime()
     * @covers \Staffbase\plugins\sdk\SSOToken::getNotBeforeTime()
     * @covers \Staffbase\plugins\sdk\SSOToken::getIssuedAtTime()
     * @covers \Staffbase\plugins\sdk\SSOToken::getIssuer()
     * @covers \Staffbase\plugins\sdk\SSOToken::getId()
     * @covers \Staffbase\plugins\sdk\SSOToken::getInstanceId()
     * @covers \Staffbase\plugins\sdk\SSOToken::getInstanceName()
     * @covers \Staffbase\plugins\sdk\SSOToken::getUserId()
     * @covers \Staffbase\plugins\sdk\SSOToken::getUserExternalId()
     * @covers \Staffbase\plugins\sdk\SSOToken::getUserUsername()
     * @covers \Staffbase\plugins\sdk\SSOToken::getUserPrimaryEmailAddress()
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
     * @covers \Staffbase\plugins\sdk\SSOToken::getSessionId()
     */
    public function testAccessorsGiveCorrectValues()
    {

        $tokenData = SSOTestData::getTokenData();
        $accessors = SSOTestData::getTokenAccessors();

        $token = SSOTokenGenerator::createSignedTokenFromData($this->privateKey, $tokenData);
        $ssoToken = new SSOToken($this->publicKey, $token);

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
