<?php

namespace Staffbase\plugins\test\SSOData;

use DateTimeImmutable;
use Exception;
use PHPUnit\Framework\TestCase;
use Staffbase\plugins\sdk\SSOData\HeaderSSODataTrait;

class HeaderSSODataTest extends TestCase
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
     * @covers \Staffbase\plugins\sdk\SSOData\SharedDataTrait::getAudience()
     * @covers \Staffbase\plugins\sdk\SSOData\SharedDataTrait::getExpireAtTime()
     * @covers \Staffbase\plugins\sdk\SSOData\SharedDataTrait::getNotBeforeTime()
     * @covers \Staffbase\plugins\sdk\SSOData\SharedDataTrait::getIssuedAtTime()
     * @covers \Staffbase\plugins\sdk\SSOData\SharedDataTrait::getId()
     * @covers \Staffbase\plugins\sdk\SSOData\SharedDataTrait::getIssuer()
     * @covers \Staffbase\plugins\sdk\SSOData\SharedDataTrait::getSubject()
     * @covers \Staffbase\plugins\sdk\SSOData\SharedDataTrait::getRole()
     * @covers \Staffbase\plugins\sdk\SSOData\HeaderSSODataTrait::getBranchId()
     * @covers \Staffbase\plugins\sdk\SSOData\HeaderSSODataTrait::getUserId()
     * @covers \Staffbase\plugins\sdk\SSOData\HeaderSSODataTrait::getTokenId()
     */
    public function testAccessorsGiveCorrectValues(): void
    {

        $tokenData = self::getTokenData();
        $accessors = self::getTokenAccessors();

        $ssoData = $this->getMockForTrait(HeaderSSODataTrait::class);

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
