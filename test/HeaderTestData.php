<?php

namespace Staffbase\plugins\test;

use DateTimeImmutable;
use Exception;

class HeaderTestData
{
    private const CLAIM_AUDIENCE                    = "aud";
    private const CLAIM_EXPIRE_AT                   = "exp";
    private const CLAIM_JWT_ID                      = "jti";
    private const CLAIM_ISSUED_AT                   = "iat";
    private const CLAIM_ISSUER                      = "iss";
    private const CLAIM_NOT_BEFORE                  = "nbf";
    private const CLAIM_SUBJECT                     = "sub";

    private const CLAIM_BRANCH_ID                   = "branchId";
    private const CLAIM_USER_ID                   = "userId";
    private const CLAIM_TOKEN_ID                   = "tokenId";

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
    public static function getTokenData(?string $exp = "10 minutes", ?string $npf = "-1 minutes", ?string $iat = "now"): array
    {
        $exp = $exp ?? "10 minutes";
        $npf = $npf ?? "-1 minutes";
        $iat = $iat ?? "now";

        $date = new DateTimeImmutable($iat);

        $tokenData = [];

        $tokenData[self::CLAIM_AUDIENCE] = "testPlugin";
        $tokenData[self::CLAIM_EXPIRE_AT] = $date->modify($exp);
        $tokenData[self::CLAIM_NOT_BEFORE] = $date->modify($npf);
        $tokenData[self::CLAIM_ISSUED_AT] = $date;
        $tokenData[self::CLAIM_ISSUER] = "external-auth-service";
        $tokenData[self::CLAIM_SUBJECT] = "user/541954c3e4b08bbdce1a340a";
        $tokenData[self::CLAIM_TOKEN_ID] = "6315e4b9b33cd4aa0a01d4b8";
        $tokenData[self::CLAIM_USER_ID] = "541954c3e4b08bbdce1a340a";
        $tokenData[self::CLAIM_BRANCH_ID] = "5f57285c65cf7c6ae49fc796";
        $tokenData[self::CLAIM_JWT_ID] = "jti-id";

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

        $accessors[self::CLAIM_AUDIENCE] = "getAudience";
        $accessors[self::CLAIM_EXPIRE_AT] = "getExpireAtTime";
        $accessors[self::CLAIM_NOT_BEFORE] = "getNotBeforeTime";
        $accessors[self::CLAIM_ISSUED_AT] = "getIssuedAtTime";
        $accessors[self::CLAIM_ISSUER] = "getIssuer";
        $accessors[self::CLAIM_SUBJECT] = "getSubject";
        $accessors[self::CLAIM_BRANCH_ID] = "getBranchId";
        $accessors[self::CLAIM_USER_ID] = "getUserId";
        $accessors[self::CLAIM_TOKEN_ID] = "getTokenId";
        $accessors[self::CLAIM_JWT_ID] = "getId";

        return $accessors;
    }
}
