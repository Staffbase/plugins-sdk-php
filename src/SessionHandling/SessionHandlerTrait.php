<?php

declare(strict_types=1);

/**
 * Trait to handle a php session. Opening, closing and destroying the session.
 * Accessing variables, stored in the session.
 *
 * PHP version 7.4
 *
 * @category  SessionHandling
 * @copyright 2017-2022 Staffbase, GmbH.
 * @author    Daniel Grosse
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 * @link      https://github.com/staffbase/plugins-sdk-php
 */

namespace Staffbase\plugins\sdk\SessionHandling;

trait SessionHandlerTrait
{
    private static string $KEY_DATA = "data";

    private ?string $pluginInstanceId = null;

    /**
     * @var String|null $sessionId the id of the current session.
     */
    private ?string $sessionId = null;

    /**
     * Open a session.
     *
     * @param string $name of the session
     * @param string $sessionId
     */
    protected function openSession(string $name = '', string $sessionId = ''): void
    {
        session_id($sessionId);

        // session_name expects a non-empty string; only set it when provided
        if ($name !== '') {
            session_name($name);
        }

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
     * @param string $key
     * @param string|null $parentKey
     *
     * @return bool
     */
    public function hasSessionVar(string $key, ?string $parentKey = null): bool
    {
        $parent = $parentKey ?? self::$KEY_DATA;
        $bucket = $this->getSessionBucket($parent);

        return $bucket !== null && isset($bucket[$key]);
    }

    /**
     * Get a previously set session variable.
     *
     * @param string $key
     * @param string|null $parentKey
     *
     * @return mixed|null
     */
    public function getSessionVar(string $key, ?string $parentKey = null)
    {
        $parent = $parentKey ?? self::$KEY_DATA;
        $bucket = $this->getSessionBucket($parent);

        return $bucket[$key] ?? null;
    }

    /**
     * Get an array of all previously set session variables.
     *
     * @param string|null $parentKey
     *
     * @return array<string,mixed>
     */
    public function getSessionData(?string $parentKey = null): array
    {
        $parent = $parentKey ?? self::$KEY_DATA;

        return $this->getSessionBucket($parent) ?? [];
    }

    /**
     * Set all session variables.
     *
     * @param array<string,mixed> $data
     * @param string|null $parentKey
     */
    public function setSessionData(array $data, ?string $parentKey = null): void
    {
        $instance = $this->getValidInstance();
        if ($instance === null) {
            return;
        }

        $parent = $parentKey ?? self::$KEY_DATA;

        /** @var array<string,mixed> $sessionInstance */
        $sessionInstance = isset($_SESSION[$instance]) && is_array($_SESSION[$instance])
            ? $_SESSION[$instance]
            : [];
        $sessionInstance[$parent] = $data;
        $_SESSION[$instance] = $sessionInstance;
    }

    /**
     * Set a session variable.
     *
     * @param string $key
     * @param mixed $val
     * @param string|null $parentKey
     */
    public function setSessionVar(string $key, mixed $val, ?string $parentKey = null): void
    {
        $instance = $this->getValidInstance();
        if ($instance === null) {
            return;
        }

        $parent = $parentKey ?? self::$KEY_DATA;

        /** @var array<string,mixed> $sessionInstance */
        $sessionInstance = isset($_SESSION[$instance]) && is_array($_SESSION[$instance])
            ? $_SESSION[$instance]
            : [];

        /** @var array<string,mixed> $bucket */
        $bucket = isset($sessionInstance[$parent]) && is_array($sessionInstance[$parent])
            ? $sessionInstance[$parent]
            : [];
        $bucket[$key] = $val;
        $sessionInstance[$parent] = $bucket;
        $_SESSION[$instance] = $sessionInstance;
    }

    /**
     * Return the validated plugin instance ID, or null if unset/empty.
     */
    private function getValidInstance(): ?string
    {
        $instance = $this->pluginInstanceId;
        return ($instance !== null && $instance !== '') ? $instance : null;
    }

    /**
     * Return the session bucket array for the given instance and parent key,
     * or null if the session structure is missing or invalid.
     *
     * @return array<string,mixed>|null
     */
    private function getSessionBucket(string $parentKey): ?array
    {
        $instance = $this->getValidInstance();
        if ($instance === null) {
            return null;
        }

        if (!isset($_SESSION[$instance]) || !is_array($_SESSION[$instance])) {
            return null;
        }

        /** @var array<string,mixed> $sessionInstance */
        $sessionInstance = $_SESSION[$instance];
        $bucket = $sessionInstance[$parentKey] ?? null;
        /** @var array<string,mixed>|null */
        return is_array($bucket) ? $bucket : null;
    }

    /**
     * Destroy the session with the given id
     *
     * @param String|null $sessionId
     * @return bool true on success or false on failure.
     */
    public function destroySession(?string $sessionId = null): bool
    {
        $sessionId = $sessionId ?: $this->sessionId;

        // save the current session
        $currentId = session_id() ?: '';
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

    private function createCompatibleSessionId(?string $input = ''): string
    {
        $string = $input ?? '';
        $notAllowedCharsPattern = '/[^a-zA-Z0-9,-]/';
        $replaced = preg_replace($notAllowedCharsPattern, '-', $string);
        return (string) $replaced;
    }
}
