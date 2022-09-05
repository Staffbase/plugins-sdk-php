<?php

namespace Staffbase\plugins\sdk\Helper;

use PHPUnit\Framework\TestCase;

class URLHelperTest extends TestCase
{

	public function setUp():void {
		$_SERVER['REQUEST_URI'] = "https://test.com/param1/12345/param2";
	}

	public function testParamWithValue() {
		$this->assertEquals("12345", URLHelper::getParam("param1"));
	}

	public function testParamWithoutValue() {
		$this->assertEquals(true, URLHelper::getParam("param2"));
	}

	public function testNotExistingParameter() {
		$this->assertEquals(null, URLHelper::getParam("param3"));
	}

	public function testNotExistingParameterWithDefault() {
		$this->assertEquals("foo", URLHelper::getParam("param3", "foo"));
	}

}
