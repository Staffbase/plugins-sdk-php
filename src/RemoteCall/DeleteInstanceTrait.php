<?php

declare(strict_types=1);

namespace Staffbase\plugins\sdk\RemoteCall;

trait DeleteInstanceTrait
{
    /**
     * Exit the script
     *
     * if a remote call was not handled by the user we die hard here
     */
    protected function exitRemoteCall(): never
    {
        error_log("Warning: The exit procedure for a remote call was not properly handled.");
        exit;
    }

    /**
     * Handle the delete-instance remote call and terminate the request.
     *
     * @param string $instanceId
     * @param RemoteCallInterface $remoteCallHandler
     *
     * @return never
     */
    private function deleteInstance(string $instanceId, RemoteCallInterface $remoteCallHandler): never
    {
        if ($remoteCallHandler instanceof DeleteInstanceCallHandlerInterface) {
            $result = $remoteCallHandler->deleteInstance($instanceId);
        } else {
            // we will accept unhandled calls with a warning
            $result = true;
            error_log("Warning: An instance deletion call for instance $instanceId was not handled.");
        }

        // finish the remote call
        if ($result) {
            $remoteCallHandler->exitSuccess();
        } else {
            $remoteCallHandler->exitFailure();
        }

        $this->exitRemoteCall();
    }
}
