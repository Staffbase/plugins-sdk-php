<?php
/**
 * SSO plugin session Test implementation, based on this doc:
 * https://developers.staffbase.com/api/plugin-sso/
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

use ReflectionClass;
use phpseclib\Crypt\RSA;
use PHPUnit\Framework\TestCase;
use SessionHandlerInterface;
use Staffbase\plugins\sdk\Exceptions\SSOAuthenticationException;
use Staffbase\plugins\sdk\Exceptions\SSOException;
use Staffbase\plugins\sdk\SSOTokenGenerator;
use Staffbase\plugins\sdk\PluginSession;
use Staffbase\plugins\sdk\RemoteCall\DeleteInstanceCallHandlerInterface;

class PluginSessionTest extends TestCase
{
    private $token;
    private $publicKey;
    private $privateKey;
    private $tokenData;
    private $classname = PluginSession::class;
    private $pluginId = 'testplugin';
    private $pluginInstanceId;

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

        $this->tokenData = SSOTestData::getTokenData();
        $this->token = SSOTokenGenerator::createSignedTokenFromData($this->privateKey, $this->tokenData);

        $this->pluginInstanceId = $this->tokenData[PluginSession::$CLAIM_INSTANCE_ID];
    }

    /**
     * Setup the environment for PluginSession.
     *
     * @param string|null $queryParamPid PID query param emulation
     * @param string|null $queryParamJwt JWT query param emulation
     * @param boolean $clearSession optionally clear out the $_SESSION array
     */
    private function setupEnvironment(string $queryParamPid = null, string $queryParamJwt = null, bool $clearSession = true)
    {

        $_REQUEST[PluginSession::QUERY_PARAM_PID] = $queryParamPid;
        $_REQUEST[PluginSession::QUERY_PARAM_JWT] = $queryParamJwt;

        if ($clearSession) {
            session_write_close();
            session_abort();
            $_SESSION = [];
        }
    }

    /**
     *
     * Test constructor works as expected.
     *
     * It allows a JWT request and further PID requests to pass
     *
     * @covers \Staffbase\plugins\sdk\PluginSession::__construct
     */
    public function testConstructorWorksAsExpected()
    {

        $this->setupEnvironment(null, $this->token);

        $mock = $this->getMockBuilder($this->classname)
            ->disableOriginalConstructor()
            ->onlyMethods(array('openSession', 'closeSession'))
            ->getMock();

        $mock->expects($this->exactly(2))
            ->method('openSession')
            ->with($this->pluginId);

        $reflectedClass = new ReflectionClass($this->classname);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke($mock, $this->pluginId, $this->publicKey);

        $this->setupEnvironment($this->pluginInstanceId, null, false);

        $constructor->invoke($mock, $this->pluginId, $this->publicKey);
    }

    /**
     *
     * Test constructor rejects spoofed PID requests.
     *
     * @covers \Staffbase\plugins\sdk\PluginSession::__construct
     */
    public function testConstructorRejectsSpoofedPID()
    {

        $mock = $this->getMockBuilder($this->classname)
            ->disableOriginalConstructor()
            ->onlyMethods(array('openSession', 'closeSession'))
            ->getMock();

        $this->setupEnvironment($this->pluginInstanceId. 'spoof', null, false);

        $this->expectException(SSOException::class);

        $reflectedClass = new ReflectionClass($this->classname);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke($mock, $this->pluginId, $this->publicKey);
    }

    /**
     *
     * Test constructor throws exception on empty pluginId.
     *
     * @covers \Staffbase\plugins\sdk\PluginSession::__construct
     */
    public function testConstructorRefuseEmptyPluginId()
    {

        $this->setupEnvironment(null, $this->token);

        $mock = $this->getMockBuilder($this->classname)
            ->disableOriginalConstructor()
            ->onlyMethods(array('openSession', 'closeSession'))
            ->getMock();

        $this->expectException(SSOException::class);
        $this->expectExceptionMessage('Empty plugin ID.');

        $reflectedClass = new ReflectionClass($this->classname);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke($mock, '', $this->publicKey);
    }

    /**
     *
     * Test constructor throws exception on empty secret.
     *
     * @covers \Staffbase\plugins\sdk\PluginSession::__construct
     */
    public function testConstructorRefuseEmptySecret()
    {

        $this->setupEnvironment(null, $this->token);

        $mock = $this->getMockBuilder($this->classname)
            ->disableOriginalConstructor()
            ->onlyMethods(array('openSession', 'closeSession'))
            ->getMock();

        $this->expectException(SSOException::class);
        $this->expectExceptionMessage('Parameter appSecret for SSOToken is empty.');

        $reflectedClass = new ReflectionClass($this->classname);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke($mock, $this->pluginId, '');
    }

    /**
     *
     * Test constructor throws exception missing both JWT and PID.
     *
     * @covers \Staffbase\plugins\sdk\PluginSession::__construct
     */
    public function testConstructorRefuseEmptyEnv()
    {

        $this->setupEnvironment(null, null);

        $mock = $this->getMockBuilder($this->classname)
            ->disableOriginalConstructor()
            ->onlyMethods(array('openSession', 'closeSession'))
            ->getMock();

        $this->expectException(SSOAuthenticationException::class);
        $this->expectExceptionMessage('Missing PID or JWT query parameter in Request.');

        $reflectedClass = new ReflectionClass($this->classname);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke($mock, $this->pluginId, $this->publicKey);
    }

    /**
     *
     * Test constructor throws exception when provided with both JWT and PID.
     *
     * @covers \Staffbase\plugins\sdk\PluginSession::__construct
     */
    public function testConstructorRefuseHavingBothJwtAndPid()
    {

        $this->setupEnvironment($this->pluginId, $this->token);

        $mock = $this->getMockBuilder($this->classname)
            ->disableOriginalConstructor()
            ->onlyMethods(array('openSession', 'closeSession'))
            ->getMock();

        $this->expectException(SSOAuthenticationException::class);
        $this->expectExceptionMessage('Tried to initialize the session with both PID and JWT provided.');

        $reflectedClass = new ReflectionClass($this->classname);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke($mock, $this->pluginId, $this->publicKey);
    }

    /**
     *
     * Test constructor updates SSO info on every JWT request.
     *
     * @covers \Staffbase\plugins\sdk\PluginSession::__construct
     */
    public function testConstructorUpdatesInfoOnJwt()
    {

        $this->setupEnvironment(null, $this->token);

        $mock = $this->getMockBuilder($this->classname)
            ->disableOriginalConstructor()
            ->onlyMethods(array('openSession', 'closeSession'))
            ->getMock();

        /** @var PluginSession $session */
        $session = new $mock($this->pluginId, $this->publicKey);

        $this->assertEquals($this->tokenData[PluginSession::$CLAIM_USER_ROLE], $session->getRole());

        $tokenData = $this->tokenData;
        $tokenData[PluginSession::$CLAIM_USER_ROLE] = 'updatedRoleName';
        $newToken = SSOTokenGenerator::createSignedTokenFromData($this->privateKey, $tokenData);

        $this->setupEnvironment(null, $newToken, false);

        /** @var PluginSession $newSession */
        $newSession = new $mock($this->pluginId, $this->publicKey);

        $this->assertEquals($newSession->getRole(), $tokenData[PluginSession::$CLAIM_USER_ROLE]);
        $this->assertEquals($session->getRole(), $newSession->getRole());
    }

    /**
     *
     * Test support for multiple instances.
     *
     * @covers \Staffbase\plugins\sdk\PluginSession::__construct
     * @covers \Staffbase\plugins\sdk\PluginSession::getSessionVar
     * @covers \Staffbase\plugins\sdk\PluginSession::setSessionVar
     */
    public function testConstructorSupportMultipleInstances()
    {

        $this->setupEnvironment(null, $this->token);

        $mock = $this->getMockBuilder($this->classname)
            ->disableOriginalConstructor()
            ->onlyMethods(array('openSession', 'closeSession'))
            ->getMock();

        /** @var PluginSession $session */
        $session = new $mock($this->pluginId, $this->publicKey);


        $tokenData = $this->tokenData;
        $tokenData[PluginSession::$CLAIM_INSTANCE_ID] = 'anotherTestInstanceId';
        $tokenData[PluginSession::$CLAIM_USER_ROLE] = 'anotherRoleInInstance';
        $newToken = SSOTokenGenerator::createSignedTokenFromData($this->privateKey, $tokenData);

        $this->setupEnvironment(null, $newToken, false);

        /** @var PluginSession $newSession */
        $newSession = new $mock($this->pluginId, $this->publicKey);

        $this->assertEquals($tokenData[PluginSession::$CLAIM_USER_ROLE], $newSession->getRole());
        $this->assertNotEquals($newSession->getRole(), $session->getRole());

        $sessionVar  = 'myvariable';
        $sessionVal  = 'mysessiontestvalue';
        $sessionVal2 = 'mysessiontestvalue2';

        $session->setSessionVar($sessionVar, $sessionVal);
        $newSession->setSessionVar($sessionVar, $sessionVal2);

        $this->assertNotEquals($session->getSessionVar($sessionVar, null), $newSession->getSessionVar($sessionVar, null));
        $this->assertEquals($sessionVal, $session->getSessionVar($sessionVar, null));
        $this->assertEquals($sessionVal2, $newSession->getSessionVar($sessionVar, null));
    }

    /**
     *
     * Test the session data is returned correctly.
     *
     * @covers \Staffbase\plugins\sdk\PluginSession::__construct
     * @covers \Staffbase\plugins\sdk\PluginSession::getSessionData
     */
    public function testGetSessionData()
    {

        $this->setupEnvironment(null, $this->token);

        $mock = $this->getMockBuilder($this->classname)
            ->disableOriginalConstructor()
            ->onlyMethods(array('openSession', 'closeSession'))
            ->getMock();

        /** @var PluginSession $session */
        $session = new $mock($this->pluginId, $this->publicKey);

        $sessionData = [
            'test1' => 'val1',
            'test2' => 'val2'
        ];


        foreach ($sessionData as $var => $val) {
            $session->setSessionVar($var, $val);
        }

        $this->assertEquals($sessionData, $session->getSessionData(null));
    }

    /**
     *
     * Test that a delete call triggers interace methods in correct order.
     *
     * @covers \Staffbase\plugins\sdk\PluginSession::__construct
     */
    public function testDeleteSuccessfulCallInterface()
    {

        $tokenData = $this->tokenData;
        $tokenData[PluginSession::$CLAIM_USER_ID] = 'delete';
        $token = SSOTokenGenerator::createSignedTokenFromData($this->privateKey, $tokenData);

        $this->setupEnvironment(null, $token, false);

        // successfull remote call handler mock
        $handler = $this->getMockBuilder(DeleteInstanceCallHandlerInterface::class)
            ->onlyMethods(array('deleteInstance', 'exitSuccess', 'exitFailure'))
            ->getMock();

        $handler->method('deleteInstance')
            ->willReturn(true);

        $handler->expects($this->once())
            ->method('deleteInstance');

        $handler->expects($this->once())
            ->method('exitSuccess');

        $handler->expects($this->never())
            ->method('exitFailure');

        // session mock
        $Session = $this->getMockBuilder($this->classname)
            ->disableOriginalConstructor()
            ->onlyMethods(array('openSession', 'closeSession', 'exitRemoteCall'))
            ->getMock();

        new $Session($this->pluginId, $this->publicKey, null, 0, $handler);
    }

    /**
     *
     * Test that a delete call triggers interace methods in correct order.
     *
     * @covers \Staffbase\plugins\sdk\PluginSession::__construct
     */
    public function testDeleteFailedCallInterface()
    {

        $tokenData = $this->tokenData;
        $tokenData[PluginSession::$CLAIM_USER_ID] = 'delete';
        $token = SSOTokenGenerator::createSignedTokenFromData($this->privateKey, $tokenData);

        $this->setupEnvironment(null, $token, false);

        // successfull remote call handler mock
        $handler = $this->getMockBuilder(DeleteInstanceCallHandlerInterface::class)
            ->onlyMethods(array('deleteInstance', 'exitSuccess', 'exitFailure'))
            ->getMock();

        $handler->method('deleteInstance')
            ->willReturn(false);

        $handler->expects($this->once())
            ->method('deleteInstance');

        $handler->expects($this->never())
            ->method('exitSuccess');

        $handler->expects($this->once())
            ->method('exitFailure');

        // session mock
        $Session = $this->getMockBuilder($this->classname)
            ->disableOriginalConstructor()
            ->onlyMethods(array('openSession', 'closeSession', 'exitRemoteCall'))
            ->getMock();

        new $Session($this->pluginId, $this->publicKey, null, 0, $handler);
    }

    /**
     *
     * Test that a session is created.
     *
     * @covers \Staffbase\plugins\sdk\PluginSession::__construct
     */
    public function testSessionIsCreated()
    {
        $tokenData = $this->tokenData;
        $this->setupEnvironment(null, $this->token, true);

        $this->assertEquals(PHP_SESSION_NONE, session_status());
        $session = new PluginSession($this->pluginId, $this->publicKey);
        $this->assertEquals(PHP_SESSION_ACTIVE, session_status());

        $this->assertEquals($tokenData[PluginSession::$CLAIM_SESSION_ID], session_id());
    }

    public function testSessionIdCheck()
    {

        $sessionHash = 'HOjLTR6+D5YIY0/waqJQp3Bg=';
        $sessionId = 'HOjLTR6-D5YIY0-waqJQp3Bg-';

        $tokenData = $this->tokenData;
        $tokenData[PluginSession::$CLAIM_SESSION_ID] = $sessionHash;
        $token = SSOTokenGenerator::createSignedTokenFromData($this->privateKey, $tokenData);

        $this->setupEnvironment(null, $token, true);

        $this->assertEquals(PHP_SESSION_NONE, session_status());
        $session = new PluginSession($this->pluginId, $this->publicKey);
        $this->assertEquals(PHP_SESSION_ACTIVE, session_status());

        $this->assertEquals($sessionId, session_id());
    }

    public function testDestroyOtherSession()
    {

        $sessionHash = 'HOjLTR6+D5YIY0/waqJQp3Bg=';
        $sessionId = 'HOjLTR6-D5YIY0-waqJQp3Bg-';

        $tokenData = $this->tokenData;
        $tokenData[PluginSession::$CLAIM_SESSION_ID] = $sessionHash;
        $token = SSOTokenGenerator::createSignedTokenFromData($this->privateKey, $tokenData);

        // successfull remote call handler mock
        $handler = $this->getMockBuilder(SessionHandlerInterface::class)
            ->setMethodsExcept()
            ->getMock();

        $handler->method('close')->willReturn(true);
        $handler->method('destroy')->willReturn(true);
        $handler->method('open')->willReturn(true);
        $handler->method('write')->willReturn(true);
        $handler->method('read')->willReturn($sessionId);

        $this->setupEnvironment(null, $token, true);

        /** @var SessionHandlerInterface $handler */
        new PluginSession($this->pluginId, $this->publicKey);

        $this->setupEnvironment(null, $this->token, false);

        /** @var PluginSession $session */
        $session = new PluginSession($this->pluginId, $this->publicKey, $handler);

        $handler->expects($this->once())
            ->method('destroy')
            ->with($sessionId);

        $handler->expects($this->exactly(2))
            ->method('write')
            ->with($this->logicalOr(
                $this->equalTo($sessionId),
                $this->equalTo($this->tokenData[PluginSession::$CLAIM_SESSION_ID])
            ));

        $handler->expects($this->exactly(2))
            ->method('open');

        $session->destroySession($sessionHash);
    }

    public function testDestroyOwnSession()
    {

        $sessionId = $this->tokenData[PluginSession::$CLAIM_SESSION_ID];
        $this->setupEnvironment(null, $this->token, false);

        // successfull remote call handler mock
        $handler = $this->getMockBuilder(SessionHandlerInterface::class)
            ->setMethodsExcept()
            ->getMock();

        $handler->method('close')->willReturn(true);
        $handler->method('destroy')->willReturn(true);
        $handler->method('open')->willReturn(true);
        $handler->method('write')->willReturn(true);
        $handler->method('read')->willReturn($sessionId);

        /** @var PluginSession $session */
        $session = new PluginSession($this->pluginId, $this->publicKey, $handler);

        $handler->expects($this->once())
            ->method('destroy')
            ->with($sessionId);

        $handler->expects($this->once())
            ->method('write')
            ->with($sessionId);

        $handler->expects($this->once())
            ->method('open');

        $session->destroySession($sessionId);
    }
}
