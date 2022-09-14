<?php
declare(strict_types=1);

/**
 * Trait to store the data of a token in in a specific property.
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

use Staffbase\plugins\sdk\SSOData\ClaimAccessTrait;

trait SessionTokenDataTrait
{
    use SessionHandlerTrait;
    use ClaimAccessTrait;

    private static string $keySso = "sso";

    /**
     * Test if a claim is set.
     *
     * @param string $claim name.
     *
     * @return boolean
     */
    protected function hasClaim(string $claim): bool
    {
        return $this->hasSessionVar($claim, self::$keySso);
    }

    /**
     * Get a claim without checking for existence.
     *
     * @param string $claim name.
     *
     * @return mixed
     */
    protected function getClaim(string $claim)
    {
        return $this->getSessionVar($claim, self::$keySso);
    }

    /**
     * Get an array of all available claims.
     *
     * @return array
     */
    protected function getAllClaims(): array
    {
        return $this->getSessionData(self::$keySso);
    }

    /**
     *
     * @param $data
     * @return void
     */
    protected function setClaims($data): void
    {
        $this->setSessionData($data, self::$keySso);
    }
}
