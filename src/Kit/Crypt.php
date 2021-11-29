<?php

declare(strict_types=1);

namespace Ep\Kit;

use Ep\Base\Config;
use InvalidArgumentException;
use RuntimeException;

final class Crypt
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    private string $method = 'AES-128-CBC';

    /**
     * @throws InvalidArgumentException
     */
    public function withMethod(string $method): self
    {
        $new = clone $this;
        $new->method = $method;
        $new->validate();
        return $new;
    }

    private ?string $key = null;

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

    private string $algo = 'sha256';

    public function withHashAlgo(string $algo): self
    {
        $new = clone $this;
        $new->algo = $algo;
        return $new;
    }

    private function getKey(): string
    {
        if ($this->key === null) {
            $this->key = base64_decode($this->config->secretKey);
            $this->validate();
        }
        return $this->key;
    }

    /**
     * @throws RuntimeException
     */
    public function encrypt(string $value): string
    {
        $length = openssl_cipher_iv_length($this->method);
        $iv = $length ? random_bytes($length) : '';

        $value = openssl_encrypt($value, $this->method, $this->getKey(), 0, $iv);
        if ($value === false) {
            throw new RuntimeException('Could not encrypt the data.');
        }

        $mac = $this->hash($iv = base64_encode($iv), $value);

        return base64_encode(json_encode(compact('iv', 'value', 'mac'), JSON_UNESCAPED_SLASHES));
    }

    /**
     * @throws RuntimeException
     */
    public function decrypt(string $payload): string
    {
        $payload = $this->getJsonPayload($payload);

        $decrypted = openssl_decrypt($payload['value'], $this->method, $this->getKey(), 0, base64_decode($payload['iv']));

        if ($decrypted === false) {
            throw new RuntimeException('Could not decrypt the data.');
        }

        return $decrypted;
    }

    public function generateKey(): string
    {
        $length = explode('-', $this->method)[1] ?? '';
        $length = is_numeric($length) ? (int) $length : 128;
        return random_bytes($length / 8);
    }

    private function validate(): void
    {
        if (!$this->config->debug) {
            return;
        }

        $method = strtolower($this->method);
        if (!in_array($method, openssl_get_cipher_methods())) {
            throw new InvalidArgumentException('Invalid cipher method "' . $this->method . '".');
        }

        $pieces = explode('-', $method);
        if (count($pieces) <= 2) {
            return;
        }

        if (!in_array(substr($pieces[2], 0, 3), ['cbc', 'cfb', 'ctr', 'ecb', 'ofb'])) {
            throw new InvalidArgumentException('The supported cipher modes are CBC, CFB, CTR, ECB and OFB.');
        }

        if (strlen($this->getKey()) !== intval($pieces[1]) / 8) {
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
        return hash_hmac($this->algo, $iv . $value, $this->getKey());
    }
}
