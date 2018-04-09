<?php

namespace Staffbase\plugins\sdk\RemoteCall;

/**
 * Interface RemoteCallInterface
 *
 * A generic interface describing the protocol with the
 * Staffbase Backend after a Remote SSO cal was issued.
 *
 * @package Staffbase\plugins\sdk\RemoteCall
 */
interface RemoteCallInterface
{
	/**
	 * Stop the execution by providing a 2XX HTTP response
	 *
	 * This will tell Staffbase that everything went OK.
	 */
	public function exitSuccess();
	/**
	 * Stop the execution by providing a 5XX HTTP response
	 * 
	 * This will tell Staffbase that it should try again later.
	 */
	public function exitFailure();
}
