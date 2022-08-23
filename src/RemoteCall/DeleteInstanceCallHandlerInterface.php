<?php
/**
 * Delete remote handler interface, based on this doc:
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
 * Interface DeleteInstanceCallHandlerInterface
 * @package Staffbase\plugins\sdk\RemoteCall
 */
interface DeleteInstanceCallHandlerInterface extends RemoteCallInterface
{
    /**
     * Method to remove and cleanup every plugin related data of the given identifier
     *
     * @param string $instanceId Plugin Instance identifier
     * @return bool False if the deletion goes wrong and should be retried later.
     */
    public function deleteInstance($instanceId);
}
