<?php

namespace Staffbase\plugins\sdk\RemoteCall;

/**
 * class AbstractRemoteCallHandler
 *
 * An Abstract RemoteCallHandler implementation
 * which can be used in conjunction with all
 * remote call interfaces
 *
 * @package Staffbase\plugins\sdk\RemoteCall
 */
abstract class AbstractRemoteCallHandler implements RemoteCallInterface
{
	/*
	 * @inheritdoc
	 */
	public function exitSuccess() {
		header("HTTP/1.1 200 OK");
		exit;
	}

	/*
	 * @inheritdoc
	 */
	public function exitFailure() {
		header('HTTP/1.1 500 Internal Server Error');
		exit;
	}
}
