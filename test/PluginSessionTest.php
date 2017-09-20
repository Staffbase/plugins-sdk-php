<?php
/**
 * SSO plugin session Test implementation, based on this doc:
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
use PHPUnit_Framework_TestCase as TestCase;
use Staffbase\plugins\sdk\PluginSession;

class PluginSessionTest extends TestCase {
	
	private $token;
	private $pubKey;
	private $privKey;
	private $tokendata;
	private $classname = PluginSession::class;
	private $pluginId = 'testplugin';
	private $pluginInstanceId;

	/**
	 * Constructor
	 * 
	 * Create an RSA-256 key pair, and set up initial token.
	 */
	public function __construct() {

		$rsa = new RSA();
		$keypair = $rsa->createKey();

		$this->pubKey  = $keypair['publickey'];
		$this->privKey = $keypair['privatekey'];

		$this->tokendata = SSODataTest::getTokenData();
		$this->token = SSOTokenTest::createSignedTokenFromData($this->privKey, $this->tokendata);

		$this->pluginInstanceId = $this->tokendata[PluginSession::CLAIM_INSTANCE_ID];
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
			->setMethods(array('openSession', 'closeSession'))
			->getMock();

		$reflectedClass = new ReflectionClass($this->classname);
		$constructor = $reflectedClass->getConstructor();
		$constructor->invoke($mock, $this->pluginId, $this->pubKey);

		$this->setupEnvironment($this->pluginInstanceId, null, false);

		$constructor->invoke($mock, $this->pluginId, $this->pubKey);
	}

	/** 
	 * @test
	 * 
	 * Test constructor rejects spoofed PID requests.
	 *
	 * @covers \Staffbase\plugins\sdk\PluginSession::__construct
	 */	
	public function testConstructorRejectsSpoofedPID() {

		$this->setupEnvironment(null,$this->token);

		$mock = $this->getMockBuilder($this->classname)
			->disableOriginalConstructor()
			->setMethods(array('openSession', 'closeSession'))
			->getMock();

		$reflectedClass = new ReflectionClass($this->classname);
		$constructor = $reflectedClass->getConstructor();
		$constructor->invoke($mock, $this->pluginId, $this->pubKey);

		$this->setupEnvironment($this->pluginInstanceId+'spoof', null, false);

		try {
			$constructor->invoke($mock, $this->pluginId, $this->pubKey);
		} catch (Exception $e) {
			return;
		}

		$this->fail();
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
			->setMethods(array('openSession', 'closeSession'))
			->getMock();

		try {

			$reflectedClass = new ReflectionClass($this->classname);
			$constructor = $reflectedClass->getConstructor();
			$constructor->invoke($mock, '', $this->pubKey);

		} catch (Exception $e) {
			return;
		}

		$this->fail();
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
			->setMethods(array('openSession', 'closeSession'))
			->getMock();

		try {

			$reflectedClass = new ReflectionClass($this->classname);
			$constructor = $reflectedClass->getConstructor();
			$constructor->invoke($mock, $this->pluginId, '');

		} catch (Exception $e) {
			return;
		}

		$this->fail();
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
			->setMethods(array('openSession', 'closeSession'))
			->getMock();

		try {

			$reflectedClass = new ReflectionClass($this->classname);
			$constructor = $reflectedClass->getConstructor();
			$constructor->invoke($mock, $this->pluginId, $this->pubKey);

		} catch (Exception $e) {
			return;
		}

		$this->fail();
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
			->setMethods(array('openSession', 'closeSession'))
			->getMock();

		try {

			$reflectedClass = new ReflectionClass($this->classname);
			$constructor = $reflectedClass->getConstructor();
			$constructor->invoke($mock, $this->pluginId, $this->pubKey);

		} catch (Exception $e) {
			return;
		}

		$this->fail();
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
			->setMethods(array('openSession', 'closeSession'))
			->getMock();

		$session = new $mock($this->pluginId, $this->pubKey);

		$this->assertEquals($session->getRole(), $this->tokendata[PluginSession::CLAIM_USER_ROLE]);

		$tokendata = $this->tokendata;
		$tokendata[PluginSession::CLAIM_USER_ROLE] = 'updatedRoleName';
		$newtoken = SSOTokenTest::createSignedTokenFromData($this->privKey, $tokendata);

		$this->setupEnvironment(null, $newtoken, false);
		$newsession = new $mock($this->pluginId, $this->pubKey);

		$this->assertEquals($newsession->getRole(), $tokendata[PluginSession::CLAIM_USER_ROLE]);
		$this->assertEquals($session->getRole(), $newsession->getRole());
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
			->setMethods(array('openSession', 'closeSession'))
			->getMock();

		$session = new $mock($this->pluginId, $this->pubKey);


		$tokendata = $this->tokendata;
		$tokendata[PluginSession::CLAIM_INSTANCE_ID] = 'anotherTestInstanceId';
		$tokendata[PluginSession::CLAIM_USER_ROLE] = 'anotherRoleInInstance';
		$newtoken = SSOTokenTest::createSignedTokenFromData($this->privKey, $tokendata);

		$this->setupEnvironment(null, $newtoken, false);

		$newsession = new $mock($this->pluginId, $this->pubKey);

		$this->assertEquals($newsession->getRole(), $tokendata[PluginSession::CLAIM_USER_ROLE]);
		$this->assertNotEquals($session->getRole(), $newsession->getRole());

		$sessionVar  = 'myvariable';
		$sessionVal  = 'mysessiontestvalue';
		$sessionVal2 = 'mysessiontestvalue2';

		$session->setSessionVar($sessionVar, $sessionVal);
		$newsession->setSessionVar($sessionVar, $sessionVal2);

		$this->assertNotEquals($session->getSessionVar($sessionVar), $newsession->getSessionVar($sessionVar));
		$this->assertEquals($session->getSessionVar($sessionVar), $sessionVal);
		$this->assertEquals($newsession->getSessionVar($sessionVar), $sessionVal2);
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
			->setMethods(array('openSession', 'closeSession'))
			->getMock();

		$session = new $mock($this->pluginId, $this->pubKey);

		$sessionData = [
			'test1' => 'val1',
			'test2' => 'val2'
		];


		foreach($sessionData as $var => $val)
			$session->setSessionVar($var, $val);

		$this->assertEquals($sessionData, $session->getSessionData());

	}
}
