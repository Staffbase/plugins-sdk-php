<?php

declare(strict_types=1);
/**
 * SSO token Test implementation, based on this doc:
 * https://developers.staffbase.com/guide/customplugin-overview
 *
 * @category  Authentication
 * @copyright 2017-2025 Staffbase SE.
 * @author    Vitaliy Ivanov
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 * @link      https://github.com/staffbase/plugins-sdk-php
 */

namespace Staffbase\plugins\test;

use DateTimeImmutable;
use phpseclib\Crypt\RSA;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Staffbase\plugins\sdk\Exceptions\SSOAuthenticationException;
use Staffbase\plugins\sdk\Exceptions\SSOException;
use Staffbase\plugins\sdk\SSOData\SSODataClaimsInterface;
use Staffbase\plugins\sdk\SSOToken;
use Staffbase\plugins\sdk\SSOTokenGenerator;

class SSOTokenTest extends TestCase
{
    private string $publicKey;
    private string $privateKey;

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
    public function testConstructorRefuseEmptySecret(): void
    {

        /** @var MockObject&SSOToken $mock */
        $mock = $this->getMockBuilder(SSOToken::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['parseToken'])
            ->getMock();

        $this->expectException(SSOException::class);
        $this->expectExceptionMessage('Parameter appSecret for SSOToken is empty.');

        $reflectedClass = new ReflectionClass(SSOToken::class);
        $constructor = $reflectedClass->getConstructor() ?: throw new \Exception('Constructor not found');
        $constructor->invoke($mock, ' ', 'fake token');
    }

    /**
     *
     * Test constructor throws exception on empty token.
     *
     * @covers \Staffbase\plugins\sdk\SSOToken::__construct
     */
    public function testConstructorRefuseEmptyToken(): void
    {

        /** @var MockObject&SSOToken $mock */
        $mock = $this->getMockBuilder(SSOToken::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['parseToken'])
            ->getMock();

        $this->expectException(SSOException::class);
        $this->expectExceptionMessage('Parameter tokenData for SSOToken is empty.');

        $reflectedClass = new ReflectionClass(SSOToken::class);
        $constructor = $reflectedClass->getConstructor() ?: throw new \Exception('Constructor not found');
        $constructor->invoke($mock, 'fake secret', ' ');
    }

    /**
     *
     * Test constructor throws exception on expired token.
     *
     * @covers \Staffbase\plugins\sdk\SSOToken::__construct
     */
    public function testConstructorToFailOnExpiredToken(): void
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
    public function testConstructorToFailOnFutureToken(): void
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
    public function testConstructorToFailOnTokenIssuedInTheFuture(): void
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
    public function testConstructorAcceptsLeewayForTokenIssuedInTheFuture(): void
    {

        $leeway = 11;
        $tokenData = SSOTestData::getTokenData(null, null, "+10 second");

        $token = SSOTokenGenerator::createSignedTokenFromData($this->privateKey, $tokenData);

        $sso = new SSOToken($this->publicKey, $token, $leeway);

        // Test passes if no exception is thrown during instantiation
    }

    /**
     *
     * Test constructor throws exception on a token missing instance id.
     *
     * @covers \Staffbase\plugins\sdk\SSOToken::__construct
     * @covers \Staffbase\plugins\sdk\Validation\HasInstanceId
     */
    public function testConstructorToFailOnMissingInstanceId(): void
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
     * Test accessors deliver correct values.
     *
     */
    public function testAccessorsGiveCorrectValues(): void
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

            $data = is_array($data) ? print_r($data, true)
                : (is_scalar($data) || is_null($data) ? (string) ($data ?? '') : '[complex_type]');

            $this->assertEquals(
                $tokenData[$key],
                $ssoToken->$fn(),
                "called $fn expected $data",
            );
        }
    }
}
