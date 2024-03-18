<?php
use Firebase\JWT\JWT;
function createJwtToken($uId)
{
    global $tokenSecretKey;

    $secretKey = $tokenSecretKey['tokenSecretKey'];
    $issuedAt = new DateTimeImmutable("now");
    $expired = $issuedAt->modify('+60 minutes')->getTimestamp();
    $serverName = "localhost";
    $id = $uId;

    $data = [
        'iat' => $issuedAt,
        'iss' => $serverName,
        'nbf' => $issuedAt->getTimestamp(),
        'exp' => $expired,
        'userId' => $id
    ];

    $jwtToken = JWT::encode(
        $data,
        $secretKey,
        'HS512'
    );
    // checkRateLimitOfApi();
    return $jwtToken;
}

?>