<?php

namespace Staffbase\plugins\sdk\SSOData;

trait SharedClaims
{
    public static string $CLAIM_AUDIENCE                    = 'aud';
    public static string $CLAIM_EXPIRE_AT                   = 'exp';
    public static string $CLAIM_JWT_ID                      = 'jti';
    public static string $CLAIM_ISSUED_AT                   = 'iat';
    public static string $CLAIM_ISSUER                      = 'iss';
    public static string $CLAIM_NOT_BEFORE                  = 'nbf';
    public static string $CLAIM_SUBJECT                     = 'sub';

    public static string $CLAIM_USER_ROLE                   = 'role';
}
