<?php
/**
 * Remote call interface, based on this doc:
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
 * Interface RemoteCallInterface
 *
 * A generic interface describing the protocol with the
 * Staffbase Backend after a Remote SSO call was issued.
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
     * Stop the execution by providing a non 2XX HTTP response
     *
     * This will tell Staffbase that it should try again later.
     */
    public function exitFailure();
}
