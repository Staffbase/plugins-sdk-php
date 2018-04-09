<?php

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