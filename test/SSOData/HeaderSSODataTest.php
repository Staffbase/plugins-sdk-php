<?php

namespace Staffbase\plugins\test\SSOData;

use DateTimeImmutable;
use Exception;
use Staffbase\plugins\sdk\SSOData\HeaderSSOData;

class HeaderSSODataTest extends \PHPUnit\Framework\TestCase
{
    private const CLAIM_AUDIENCE                    = 'aud';
    private const CLAIM_EXPIRE_AT                   = 'exp';
    private const CLAIM_JWT_ID                      = 'jti';
    private const CLAIM_ISSUED_AT                   = 'iat';
    private const CLAIM_ISSUER                      = 'iss';
    private const CLAIM_NOT_BEFORE                  = 'nbf';
    private const CLAIM_SUBJECT                     = 'sub';

    private const CLAIM_BRANCH_ID = "branchId";
    private const CLAIM_USER_ID = "userId";
    private const CLAIM_TOKEN_ID = "tokenId";

    /**
     * Create test data for a token.
     *
     * Can be used in development in conjunction with
     * createSignedTokenFromData to issue development tokens.
     *
     * @param string|null $exp
     * @param string|null $npf
     * @param string|null $iat
     * @return array Associative array of claims.
     * @throws Exception
     */
    public static function getTokenData(?string $exp = '10 minutes', ?string $npf = '-1 minutes', ?string $iat = 'now'): array
    {
        $exp = $exp ?? '10 minutes';
        $npf = $npf ?? '-1 minutes';
        $iat = $iat ?? 'now';

        $date = new DateTimeImmutable($iat);

        $tokenData = [];

        $tokenData[self::CLAIM_AUDIENCE] = 'testPlugin';
        $tokenData[self::CLAIM_EXPIRE_AT] = $date->modify($exp);
        $tokenData[self::CLAIM_NOT_BEFORE] = $date->modify($npf);
        $tokenData[self::CLAIM_ISSUED_AT] = $date;
        $tokenData[self::CLAIM_ISSUER] = 'api.staffbase.com';
        $tokenData[self::CLAIM_SUBJECT] = 'user/user-id';
        $tokenData[self::CLAIM_BRANCH_ID] = "dev-id";
        $tokenData[self::CLAIM_JWT_ID] = "jti-id";
        $tokenData[self::CLAIM_USER_ID] = 'user-id';
        $tokenData[self::CLAIM_TOKEN_ID] = 'token-id';

        return $tokenData;
    }

    /**
     * Get accessors map for supported tokens.
     *
     * @return array Associative array of claim accessors.
     */
    public static function getTokenAccessors(): array
    {

        $accessors = [];

        $accessors[self::CLAIM_AUDIENCE] = 'getAudience';
        $accessors[self::CLAIM_EXPIRE_AT] = 'getExpireAtTime';
        $accessors[self::CLAIM_NOT_BEFORE] = 'getNotBeforeTime';
        $accessors[self::CLAIM_ISSUED_AT] = 'getIssuedAtTime';
        $accessors[self::CLAIM_ISSUER] = 'getIssuer';
        $accessors[self::CLAIM_SUBJECT] = 'getSubject';
        $accessors[self::CLAIM_BRANCH_ID] = "getBranchId";
        $accessors[self::CLAIM_JWT_ID] = 'getId';
        $accessors[self::CLAIM_USER_ID] = 'getUserId';
        $accessors[self::CLAIM_TOKEN_ID] = 'getTokenId';

        return $accessors;
    }

    /**
     *
     * Test accessors deliver correct values.
     *
     * @covers \Staffbase\plugins\sdk\SSOData\SharedData::getAudience()
     * @covers \Staffbase\plugins\sdk\SSOData\SharedData::getExpireAtTime()
     * @covers \Staffbase\plugins\sdk\SSOData\SharedData::getNotBeforeTime()
     * @covers \Staffbase\plugins\sdk\SSOData\SharedData::getIssuedAtTime()
     * @covers \Staffbase\plugins\sdk\SSOData\SharedData::getId()
     * @covers \Staffbase\plugins\sdk\SSOData\SharedData::getIssuer()
     * @covers \Staffbase\plugins\sdk\SSOData\SharedData::getSubject()
     * @covers \Staffbase\plugins\sdk\SSOData\SharedData::getRole()
     * @covers \Staffbase\plugins\sdk\SSOData\HeaderSSOData::getBranchId()
     * @covers \Staffbase\plugins\sdk\SSOData\HeaderSSOData::getUserId()
     * @covers \Staffbase\plugins\sdk\SSOData\HeaderSSOData::getTokenId()
     */
    public function testAccessorsGiveCorrectValues(): void
    {

        $tokenData = self::getTokenData();
        $accessors = self::getTokenAccessors();

        $ssoData = $this->getMockForTrait(HeaderSSOData::class);

        $ssoData
            ->expects($this->exactly(count($accessors)))
            ->method('hasClaim')
            ->willReturnCallback(function ($key) use ($tokenData) {
                return isset($tokenData[$key]);
            });

        $ssoData
            ->expects($this->exactly(count($accessors)))
            ->method('getClaim')
            ->willReturnCallback(function ($key) use ($tokenData) {
                return $tokenData[$key];
            });

        foreach ($accessors as $key => $fn) {
            $this->assertEquals(
                $ssoData->$fn(),
                $tokenData[$key],
                "called $fn expected " .
                is_array($tokenData[$key]) ? print_r($tokenData[$key], true) : $tokenData[$key]
            );
        }
    }
}
