<?php

declare(strict_types=1);

namespace Ep\Kit;

use Ep\Base\Config;
use InvalidArgumentException;
use JsonException;
use RuntimeException;

final class Crypt
{
    private Config $config;
    private string $key;
    private string $cipher;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->key = base64_decode($config->secretKey);
        $this->cipher = $config->algoCipher;
        $this->validate();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function withKey(string $key): self
    {
        $new = clone $this;
        $new->key = $key;
        $new->validate();
        return $new;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function withCipher(string $cipher): self
    {
        $new = clone $this;
        $new->cipher = $cipher;
        $new->validate();
        return $new;
    }

    /**
     * @throws JsonException
     * @throws RuntimeException
     */
    public function encrypt(string $value): string
    {
        $length = openssl_cipher_iv_length($this->cipher);
        $iv = $length ? random_bytes($length) : '';

        $value = openssl_encrypt($value, $this->cipher, $this->key, 0, $iv);
        if ($value === false) {
            throw new RuntimeException('Could not encrypt the data.');
        }

        $iv = base64_encode($iv);

        $mac = $this->hash($iv, $value);

        return base64_encode(json_encode(compact('iv', 'value', 'mac'), JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }

    /**
     * @throws RuntimeException
     */
    public function decrypt(string $payload): string
    {
        $payload = $this->getJsonPayload($payload);

        $iv = base64_decode($payload['iv']);

        $decrypted = openssl_decrypt($payload['value'], $this->cipher, $this->key, 0, $iv);

        if ($decrypted === false) {
            throw new RuntimeException('Could not decrypt the data.');
        }

        return $decrypted;
    }

    public function generateKey(): string
    {
        $length = explode('-', $this->cipher)[1] ?? '';
        $length = is_numeric($length) ? (int) $length : 128;
        return random_bytes($length / 8);
    }

    private function validate(): void
    {
        if (!$this->config->debug || $this->config->isEp()) {
            return;
        }

        $cipher = strtolower($this->cipher);
        if (!in_array($cipher, openssl_get_cipher_methods())) {
            throw new InvalidArgumentException('Invalid cipher "' . $this->cipher . '".');
        }

        $pieces = explode('-', $cipher);
        if (count($pieces) <= 2) {
            return;
        }

        if (!in_array(substr($pieces[2], 0, 3), ['cbc', 'cfb', 'ctr', 'ecb', 'ofb'])) {
            throw new InvalidArgumentException('The supported cipher modes are CBC, CFB, CTR, ECB and OFB.');
        }

        if (strlen($this->key) !== intval($pieces[1]) / 8) {
            throw new InvalidArgumentException('The secret key length is not correct.');
        }
    }

    private function getJsonPayload(string $payload): array
    {
        $payload = json_decode(base64_decode($payload), true);

        if (!$this->validatePayload($payload)) {
            throw new RuntimeException('The payload is invalid.');
        }

        if (!$this->validateMac($payload)) {
            throw new RuntimeException('The MAC is invalid.');
        }

        return $payload;
    }

    /**
     * @param mixed $payload
     */
    private function validatePayload($payload): bool
    {
        return is_array($payload) && isset($payload['iv'], $payload['value'], $payload['mac']);
    }

    private function validateMac(array $payload): bool
    {
        return hash_equals(
            $this->hash($payload['iv'], $payload['value']),
            $payload['mac']
        );
    }

    private function hash(string $iv, string $value): string
    {
        return hash_hmac('sha256', $iv . $value, $this->key);
    }
}
