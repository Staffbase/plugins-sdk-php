<?php

namespace Staffbase\plugins\test;

use InvalidArgumentException;
use phpseclib\Crypt\RSA;
use Staffbase\plugins\sdk\Exceptions\SSOAuthenticationException;
use Staffbase\plugins\sdk\Exceptions\SSOException;
use Staffbase\plugins\sdk\HeaderSession;
use PHPUnit\Framework\TestCase;
use Staffbase\plugins\sdk\PluginSession;
use Staffbase\plugins\sdk\SSOTokenGenerator;

class HeaderSessionTest extends TestCase
{
    private $token;
    private $publicKey;
    private $privateKey;
    private $tokenData;
    private $pluginId = 'testplugin';
    private $pluginInstanceId = "6213a5905988e6612ff08cb4";

    private const BASE_URL = "https://test.com";

    /**
     * Constructor
     *
     * Create an RSA-256 key pair, and set up initial token.
     */
    public function __construct()
    {

        parent::__construct();
        $rsa = new RSA();
        $keypair = $rsa->createKey(2048);

        $this->publicKey  = $keypair['publickey'];
        $this->privateKey = $keypair['privatekey'];

        $this->tokenData = HeaderTestData::getTokenData();
        $this->token = SSOTokenGenerator::createSignedTokenFromData($this->privateKey, $this->tokenData);
    }

    protected function tearDown(): void
    {
        unset($_SERVER['REQUEST_URI'], $_SERVER['HTTP_AUTHORIZATION']);
    }


    /**
     * Setup the environment for PluginSession.
     *
     * @param string|null $instanceId
     * @param string|null $jwt JWT query param emulation
     * @param bool $isStudio
     */
    private function setupEnvironment(
        string $instanceId = null,
        string $jwt = null,
        bool $isStudio = false
    ) {

        $_SERVER['REQUEST_URI'] = self::BASE_URL . "/$this->pluginId/$instanceId" . ($isStudio ? "/studio" : "");

        if ($jwt) {
            $_SERVER['HTTP_AUTHORIZATION'] = "Bearer $jwt";
        }
    }

    /**
     *
     * Test constructor works as expected.
     *
     * It allows a JWT request and further PID requests to pass
     *
     * @covers \Staffbase\plugins\sdk\HeaderSession::__construct
     */
    public function testConstructorWorksAsExpected()
    {
        $this->setupEnvironment($this->pluginInstanceId, $this->token);

        $headerSession = new HeaderSession($this->pluginId, $this->publicKey);

        $this->assertEquals($this->tokenData["userId"], $headerSession->getUserId());
        $this->assertEquals($this->tokenData["tokenId"], $headerSession->getTokenId());
        $this->assertEquals($this->tokenData["branchId"], $headerSession->getBranchId());
    }

    /**
     *
     * Test constructor throws exception on empty pluginId.
     *
     * @covers \Staffbase\plugins\sdk\HeaderSession::__construct
     */
    public function testConstructorRefuseEmptyPluginId()
    {

        $this->setupEnvironment(null, $this->token);

        $this->expectException(SSOException::class);
        $this->expectExceptionMessage('Empty plugin ID.');

        new HeaderSession("", $this->publicKey);
    }

    /**
     *
     * Test constructor throws exception on invalid / missing instance id.
     *
     * @covers \Staffbase\plugins\sdk\HeaderSession::validateParams
     * @covers \Staffbase\plugins\sdk\HeaderSession::isValidInstanceId
     */
    public function testConstructorRefuseInvalidInstanceId()
    {

        $this->setupEnvironment("invalid999", $this->token);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Instance ID is not valid: invalid999");

        new HeaderSession($this->pluginId, $this->publicKey);

        $this->setupEnvironment("invalid999", $this->token);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Instance ID is not valid: ");

        new HeaderSession($this->pluginId, $this->publicKey);
    }

    /**
     *
     * Test constructor throws exception on missing authorization field.
     *
     * @covers \Staffbase\plugins\sdk\HeaderSession::getHeaderAuthorizationToken
     * @covers \Staffbase\plugins\sdk\HeaderSession::getHeaders
     * @covers \Staffbase\plugins\sdk\HeaderSession::getToken
     */
    public function testHeaderFieldException(): void
    {
        $this->setupEnvironment($this->pluginInstanceId, null);


        $this->expectException(SSOAuthenticationException::class);
        $this->expectExceptionMessage("No Authorization field set.");

        new HeaderSession($this->pluginId, $this->publicKey);
    }

    /**
     *
     * Test constructor throws exception on missing authorization field.
     *
     * @covers \Staffbase\plugins\sdk\HeaderSession::isUserView
     * @covers \Staffbase\plugins\sdk\HeaderSession::isAdminView
     */
    public function testEditorUserDifferentiation(): void
    {
        $this->setupEnvironment($this->pluginInstanceId, $this->token);

        $headerSession = new HeaderSession($this->pluginId, $this->publicKey);

        $this->assertTrue($headerSession->isUserView());

        $tokenData = $this->tokenData;
        $tokenData[PluginSession::$CLAIM_USER_ROLE] = 'editor';
        $newToken = SSOTokenGenerator::createSignedTokenFromData($this->privateKey, $tokenData);

        $this->setupEnvironment($this->pluginInstanceId, $newToken, true);

        $headerSession = new HeaderSession($this->pluginId, $this->publicKey);
        $this->assertFalse($headerSession->isUserView());
    }
}
