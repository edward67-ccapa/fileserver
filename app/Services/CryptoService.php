<?php

namespace App\Services;

class CryptoService
{
    protected string $secretKey;
    protected string $cipher = 'AES-256-CBC';

    public function __construct(?string $key = null)
    {
        $rawKey = $key ?? config('app.key') ?? env('APP_KEY');
        if (str_starts_with($rawKey, 'base64:')) {
            $rawKey = base64_decode(substr($rawKey, 7));
        }
        // Derive 32-byte (256-bit) binary key using SHA-256
        $this->secretKey = hash('sha256', $rawKey, true);
    }

    /**
     * Encrypt array or string payload into a secure, URL-safe Base64 token.
     *
     * @param mixed $data
     * @return string
     */
    public function encrypt(mixed $data): string
    {
        $payload = json_encode([
            'data' => $data,
            'timestamp' => time(),
        ]);

        $iv = random_bytes(16);
        $encryptedRaw = openssl_encrypt($payload, $this->cipher, $this->secretKey, OPENSSL_RAW_DATA, $iv);

        if ($encryptedRaw === false) {
            throw new \RuntimeException('Falló la encriptación de datos.');
        }

        // Generate HMAC-SHA256 signature for data integrity
        $hmac = hash_hmac('sha256', $iv . $encryptedRaw, $this->secretKey, true);

        // Pack: IV (16 bytes) + HMAC (32 bytes) + Encrypted Payload
        $combined = $iv . $hmac . $encryptedRaw;

        return $this->base64UrlEncode($combined);
    }

    /**
     * Decrypt URL-safe Base64 token back into array payload.
     *
     * @param string $token
     * @return mixed
     */
    public function decrypt(string $token): mixed
    {
        $combined = $this->base64UrlDecode($token);

        if ($combined === false || strlen($combined) < 48) {
            throw new \InvalidArgumentException('Token de encriptación inválido o corrupto.');
        }

        $iv = substr($combined, 0, 16);
        $hmac = substr($combined, 16, 32);
        $encryptedRaw = substr($combined, 48);

        // Verify HMAC signature
        $expectedHmac = hash_hmac('sha256', $iv . $encryptedRaw, $this->secretKey, true);
        if (!hash_equals($expectedHmac, $hmac)) {
            throw new \SecurityException('Firma de seguridad inválida. El token ha sido alterado.');
        }

        $decryptedRaw = openssl_decrypt($encryptedRaw, $this->cipher, $this->secretKey, OPENSSL_RAW_DATA, $iv);

        if ($decryptedRaw === false) {
            throw new \RuntimeException('Falló la desencriptación de datos.');
        }

        $decoded = json_decode($decryptedRaw, true);
        return $decoded['data'] ?? $decoded;
    }

    /**
     * Verify if an encrypted authorization token permits CRUD operations.
     *
     * @param string|null $token
     * @param int|null $maxAgeSeconds Optional expiration window in seconds
     * @return bool
     */
    public function verifyCrudPermission(?string $token, ?int $maxAgeSeconds = null): bool
    {
        if (empty($token)) {
            return false;
        }

        try {
            $decrypted = $this->decrypt($token);

            // Allow string permissions like "crud", "allow_all", "upload"
            if (is_string($decrypted)) {
                return in_array(strtolower($decrypted), ['crud', 'allow_all', 'upload', 'granted', 'true', '1']);
            }

            // Allow array permission payload e.g. ['permission' => 'crud', 'granted' => true]
            if (is_array($decrypted)) {
                $perm = strtolower($decrypted['permission'] ?? $decrypted['permiso'] ?? '');
                $granted = $decrypted['granted'] ?? $decrypted['permitido'] ?? true;

                if (!$granted) {
                    return false;
                }

                if (!empty($perm) && !in_array($perm, ['crud', 'allow_all', 'upload', 'granted', 'true'])) {
                    return false;
                }

                // Verify expiration if timestamp is embedded
                if ($maxAgeSeconds !== null && isset($decrypted['timestamp'])) {
                    if (time() - $decrypted['timestamp'] > $maxAgeSeconds) {
                        return false; // Token expired
                    }
                }

                return true;
            }

            return false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    protected function base64UrlDecode(string $data): string|false
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}
