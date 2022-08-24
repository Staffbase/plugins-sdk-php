<?php

namespace Staffbase\plugins\sdk\SessionHandling;

trait SessionHandlerTrait
{

    private static string $KEY_DATA = "data";

    private ?string $pluginInstanceId = null;

    /**
     * Open a session.
     *
     * @param string $name of the session
     */
    protected function openSession(string $name, ?string $sessionId): void
    {
        session_id($sessionId);
        session_name($name);
        session_start();
    }

    /**
     * Close a session.
     */
    protected function closeSession(): void
    {
        session_write_close();
    }

    /**
     * Checks if the given key is set
     *
     * @param string|null $parentKey
     *
     * @return bool
     */
    public function hasSessionVar($key, ?string $parentKey = null): bool
    {
        return isset($_SESSION[$this->pluginInstanceId][$parentKey ?? self::$KEY_DATA][$key]);
    }

    /**
     * Get a previously set session variable.
     *
     * @param mixed $key
     * @param string|null $parentKey
     *
     * @return mixed|null
     */
    public function getSessionVar($key, ?string $parentKey = null)
    {
        return $_SESSION[$this->pluginInstanceId][$parentKey ?? self::$KEY_DATA][$key] ?? null;
    }

    /**
     * Get an array of all previously set session variables.
     *
     * @param string|null $parentKey
     *
     * @return array
     */
    public function getSessionData(?string $parentKey = null): array
    {
        return $_SESSION[$this->pluginInstanceId][$parentKey ?? self::$KEY_DATA] ?? [];
    }

    /**
     * Set all session variables.
     *
     * @param mixed $data
     * @param string|null $parentKey
     *
     */
    public function setSessionData($data, ?string $parentKey = null): void
    {
        $_SESSION[$this->pluginInstanceId][$parentKey ?? self::$KEY_DATA] = $data;
    }

    /**
     * Set a session variable.
     *
     * @param mixed $key
     * @param mixed $val
     * @param string|null $parentKey
     */
    public function setSessionVar($key, $val, ?string $parentKey = null): void
    {
        $_SESSION[$this->pluginInstanceId][$parentKey ?? self::$KEY_DATA][$key] = $val;
    }


    /**
     * Destroy the session with the given id
     *
     * @param String|null $sessionId
     * @return bool true on success or false on failure.
     */
    public function destroySession(String $sessionId = null): bool
    {
        $sessionId = $sessionId ?: $this->sessionId;

        // save the current session
        $currentId = session_id();
        session_write_close();

        // switch to the target session and removes it
        session_id($this->createCompatibleSessionId($sessionId));
        session_start();
        $result = session_destroy();

        // switches back to the original session
        if ($currentId !== $sessionId) {
            session_id($currentId);
            session_start();
        }

        return $result;
    }

    private function createCompatibleSessionId(String $string): String
    {
        $allowedChars = '/[^a-zA-Z0-9,-]/';
        return preg_replace($allowedChars, '-', $string);
    }
}
