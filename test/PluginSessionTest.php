<?php
/**
 * SSO plugin session Test implementation, based on this doc:
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

use ReflectionClass;
use phpseclib\Crypt\RSA;
use PHPUnit\Framework\TestCase;
use Staffbase\plugins\sdk\Exceptions\SSOAuthenticationException;
use Staffbase\plugins\sdk\Exceptions\SSOException;
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
	public function __construct() {

	    parent::__construct();
		$rsa = new RSA();
		$keypair = $rsa->createKey();

		$this->publicKey  = $keypair['publickey'];
		$this->privateKey = $keypair['privatekey'];

		$this->tokenData = SSODataTest::getTokenData();
		$this->token = SSOTokenTest::createSignedTokenFromData($this->privateKey, $this->tokenData);

		$this->pluginInstanceId = $this->tokenData[PluginSession::CLAIM_INSTANCE_ID];
	}

	/**
	 * Setup the environment for PluginSession.
	 *
	 * @param string $queryParamPid PID query param emulation
	 * @param string $queryParamJwt JWT query param emulation
	 * @param boolean $clearSession optionally clear out the $_SESSION array
	 */
	private function setupEnvironment($queryParamPid = null, $queryParamJwt = null, $clearSession = true) {

		$_GET[PluginSession::QUERY_PARAM_PID] = $queryParamPid;
		$_GET[PluginSession::QUERY_PARAM_JWT] = $queryParamJwt;

		if($clearSession)
			$_SESSION = [];
	}

	/**
	 * @test
	 *
	 * Test constructor works as expected.
	 *
	 * It allows a JWT request and further PID requests to pass
	 *
	 * @covers \Staffbase\plugins\sdk\PluginSession::__construct
	 */
	public function testConstructorWorksAsExpected() {

		$this->setupEnvironment(null,$this->token);

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
	 * @test
	 *
	 * Test constructor rejects spoofed PID requests.
	 *
	 * @covers \Staffbase\plugins\sdk\PluginSession::__construct
	 */
	public function testConstructorRejectsSpoofedPID() {

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
	 * @test
	 *
	 * Test constructor throws exception on empty pluginId.
	 *
	 * @covers \Staffbase\plugins\sdk\PluginSession::__construct
	 */
	public function testConstructorRefuseEmptyPluginId() {

		$this->setupEnvironment(null,$this->token);

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
	 * @test
	 *
	 * Test constructor throws exception on empty secret.
	 *
	 * @covers \Staffbase\plugins\sdk\PluginSession::__construct
	 */
	public function testConstructorRefuseEmptySecret() {

		$this->setupEnvironment(null,$this->token);

		$mock = $this->getMockBuilder($this->classname)
			->disableOriginalConstructor()
			->onlyMethods(array('openSession', 'closeSession'))
			->getMock();

        $this->expectException(SSOException::class);
        $this->expectExceptionMessage('Empty app secret.');

        $reflectedClass = new ReflectionClass($this->classname);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke($mock, $this->pluginId, '');
	}

	/**
	 * @test
	 *
	 * Test constructor throws exception missing both JWT and PID.
	 *
	 * @covers \Staffbase\plugins\sdk\PluginSession::__construct
	 */
	public function testConstructorRefuseEmptyEnv() {

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
	 * @test
	 *
	 * Test constructor throws exception when provided with both JWT and PID.
	 *
	 * @covers \Staffbase\plugins\sdk\PluginSession::__construct
	 */
	public function testConstructorRefuseHavingBothJwtAndPid() {

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
	 * @test
	 *
	 * Test constructor updates SSO info on every JWT request.
	 *
	 * @covers \Staffbase\plugins\sdk\PluginSession::__construct
	 */
	public function testConstructorUpdatesInfoOnJwt() {

		$this->setupEnvironment(null,$this->token);

		$mock = $this->getMockBuilder($this->classname)
			->disableOriginalConstructor()
			->onlyMethods(array('openSession', 'closeSession'))
			->getMock();

        /** @var PluginSession $session */
        $session = new $mock($this->pluginId, $this->publicKey);

		$this->assertEquals($session->getRole(), $this->tokenData[PluginSession::CLAIM_USER_ROLE]);

		$tokenData = $this->tokenData;
		$tokenData[PluginSession::CLAIM_USER_ROLE] = 'updatedRoleName';
		$newToken = SSOTokenTest::createSignedTokenFromData($this->privateKey, $tokenData);

		$this->setupEnvironment(null, $newToken, false);

        /** @var PluginSession $newSession */
        $newSession = new $mock($this->pluginId, $this->publicKey);

        /** @var PluginSession $newSession */
		$newSession = new $mock($this->pluginId, $this->publicKey);

		$this->assertEquals($newSession->getRole(), $tokenData[PluginSession::CLAIM_USER_ROLE]);
		$this->assertEquals($session->getRole(), $newSession->getRole());
	}

	/**
	 * @test
	 *
	 * Test support for multiple instances.
	 *
	 * @covers \Staffbase\plugins\sdk\PluginSession::__construct
	 * @covers \Staffbase\plugins\sdk\PluginSession::getSessionVar
	 * @covers \Staffbase\plugins\sdk\PluginSession::setSessionVar
	 */
	public function testConstructorSupportMultipleInstances() {

		$this->setupEnvironment(null,$this->token);

		$mock = $this->getMockBuilder($this->classname)
			->disableOriginalConstructor()
			->onlyMethods(array('openSession', 'closeSession'))
			->getMock();

        /** @var PluginSession $session */
        $session = new $mock($this->pluginId, $this->publicKey);


		$tokenData = $this->tokenData;
		$tokenData[PluginSession::CLAIM_INSTANCE_ID] = 'anotherTestInstanceId';
		$tokenData[PluginSession::CLAIM_USER_ROLE] = 'anotherRoleInInstance';
		$newToken = SSOTokenTest::createSignedTokenFromData($this->privateKey, $tokenData);

		$this->setupEnvironment(null, $newToken, false);

        /** @var PluginSession $newSession */
        $newSession = new $mock($this->pluginId, $this->publicKey);

		$this->assertEquals($newSession->getRole(), $tokenData[PluginSession::CLAIM_USER_ROLE]);
		$this->assertNotEquals($session->getRole(), $newSession->getRole());

		$sessionVar  = 'myvariable';
		$sessionVal  = 'mysessiontestvalue';
		$sessionVal2 = 'mysessiontestvalue2';

		$session->setSessionVar($sessionVar, $sessionVal);
		$newSession->setSessionVar($sessionVar, $sessionVal2);

		$this->assertNotEquals($session->getSessionVar($sessionVar), $newSession->getSessionVar($sessionVar));
		$this->assertEquals($session->getSessionVar($sessionVar), $sessionVal);
		$this->assertEquals($newSession->getSessionVar($sessionVar), $sessionVal2);
	}

	/**
	 * @test
	 *
	 * Test the session data is returned correctly.
	 *
	 * @covers \Staffbase\plugins\sdk\PluginSession::__construct
	 * @covers \Staffbase\plugins\sdk\PluginSession::getSessionData
	 */
	public function testGetSessionData() {

		$this->setupEnvironment(null,$this->token);

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


		foreach($sessionData as $var => $val)
			$session->setSessionVar($var, $val);

		$this->assertEquals($sessionData, $session->getSessionData());

	}

	/**
	 * @test
	 *
	 * Test that a delete call triggers interace methods in correct order.
	 *
	 * @covers \Staffbase\plugins\sdk\PluginSession::__construct
	 */
	public function testDeleteSuccessfulCallInterface() {

		$tokenData = $this->tokenData;
		$tokenData[PluginSession::CLAIM_USER_ID] = 'delete';
		$token = SSOTokenTest::createSignedTokenFromData($this->privateKey, $tokenData);

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
	 * @test
	 *
	 * Test that a delete call triggers interace methods in correct order.
	 *
	 * @covers \Staffbase\plugins\sdk\PluginSession::__construct
	 */
	public function testDeleteFailedCallInterface() {

		$tokenData = $this->tokenData;
		$tokenData[PluginSession::CLAIM_USER_ID] = 'delete';
		$token = SSOTokenTest::createSignedTokenFromData($this->privateKey, $tokenData);

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

}
