<?php
/**
 * Abstract remote handler implementation, based on this doc:
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
    /**
     * Stop the execution by providing a 2XX HTTP response
     *
     * This will tell Staffbase that everything went OK.
     */
    public function exitSuccess()
    {
        header("HTTP/1.1 200 OK");
        exit;
    }

    /**
     * Stop the execution by providing a 5XX HTTP response
     *
     * This will tell Staffbase that it should try again later.
     */
    public function exitFailure()
    {
        header('HTTP/1.1 500 Internal Server Error');
        exit;
    }
}
