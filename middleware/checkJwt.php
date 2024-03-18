<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\BeforeValidException;
use firebase\JWT\SignatureInvalidException;

function tokenValid()
{
    $status = 200;
    $message = "";

    try {
        $header = getallheaders();
        $jwt = explode(" ", $header['Authorization'])[1];

        if (! $jwt) {
            throw new Exception("HTTP/1.0 400 Bad Request", 400);
        }
        global $tokenSecretKey;

        $secretKey = $tokenSecretKey['tokenSecretKey'];
        $token = JWT::decode($jwt, new Key($secretKey, 'HS512'));
        $now = new DateTimeImmutable();
        $serverName = 'localhost';

        if ($token->iss !== $serverName ||
        $token->nbf > $now->getTimestamp() ||
        $token->exp < $now->getTimestamp())
        {
            throw new Exception('HTTP/1.1 401 Unauthorized', 401);
        }
    } catch (ExpiredException $e) {
        $status = 400;
        $message = $e->getMessage();
    } catch (SignatureInvalidException $e) {
        $status = 400;
        $message = 'Invalid token signature ' . $e->getMessage();
    } catch (BeforeValidException $e) {
        $status = 400;
        $message = 'Token not valid yet ' . $e->getMessage();
    } catch (Exception $e) {
        $status = 400;
        $message = 'Invalid token ' . $e->getMessage();
    } finally {
        http_response_code($status);
        if ($status == 400 || $status == 404 || $status == 401) {
            $response = [
                'status' => $status,
                'message' => $message
            ];
            header('content-type: application/json');
            echo json_encode($response);
        } else {
            return $token->userId;
        }
    }
}

?>