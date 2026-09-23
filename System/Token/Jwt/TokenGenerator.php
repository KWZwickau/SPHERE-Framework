<?php
declare(strict_types=1);

namespace SPHERE\System\Token\Jwt;

use ReallySimpleJWT\Token;
use SPHERE\System\Token\Type\Jwt;

/**
 * Class TokenGenerator
 */
class TokenGenerator
{
    public static function createToken(
        string $issuer = null,
        int $timeout = null,
        array $payload = []
    ): ?string {

        /** @var Jwt $jwt */
        $jwt = (new \SPHERE\System\Token\Token(new Jwt()))->getToken();

        $payload = array_merge([
            'iat' => time(),
            'exp' => time() + $timeout,
            'iss' => $issuer,
        ], $payload);

        // Encrypt password
        if (isset($payload['credentialPassword'])) {
            $payload['credentialPassword'] = openssl_encrypt(
                $payload['credentialPassword'], 'AES-128-ECB', $jwt->getJwtSecret()
            );
        }

        return Token::customPayload($payload, $jwt->getJwtSecret());
    }

    public static function readToken(string $token): ?array
    {
        if (!self::validateToken($token)) {
            return null;
        }

       $payload = Token::getPayload($token);

        // Decrypt password
        if (isset($payload['credentialPassword'])) {
            /** @var Jwt $jwt */
            $jwt = (new \SPHERE\System\Token\Token(new Jwt()))->getToken();

            $payload['credentialPassword'] = openssl_decrypt(
                $payload['credentialPassword'], 'AES-128-ECB', $jwt->getJwtSecret()
            );
        }

        return $payload;
    }

    public static function validateToken(string $token): bool
    {
        /** @var Jwt $jwt */
        $jwt = (new \SPHERE\System\Token\Token(new Jwt()))->getToken();

        if (!Token::validate($token, $jwt->getJwtSecret())) {
            return false;
        }

        if (!Token::validateExpiration($token)) {
            return false;
        }
        return true;
    }
}
