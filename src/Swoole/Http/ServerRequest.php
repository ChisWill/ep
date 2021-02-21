<?php

declare(strict_types=1);

namespace Ep\Swoole\Http;

use Swoole\Http\Request;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use RuntimeException;

final class ServerRequest implements ServerRequestInterface
{
    private Request $request;

    private array $attributes = [];

    private string $method = 'GET';

    private string $protocol = '1.1';

    private ?string $requestTarget = null;

    private array $uploadedFiles;

    private UriInterface $uri;

    private StreamInterface $stream;

    private UriFactoryInterface $uriFactory;
    private UploadedFileFactoryInterface $uploadedFileFactory;
    private StreamFactoryInterface $streamFactory;

    public function __construct(
        UriFactoryInterface $uriFactory,
        UploadedFileFactoryInterface $uploadedFileFactory,
        StreamFactoryInterface $streamFactory
    ) {
        $this->uriFactory = $uriFactory;
        $this->uploadedFileFactory = $uploadedFileFactory;
        $this->streamFactory = $streamFactory;
    }

    public function create(Request $request): self
    {
        $new = clone $this;

        $new->request = $request;
        $new->method = $request->server['request_method'] ?? $new->method;
        $new->protocol = $new->initProtocolVersion();
        $new->uploadedFiles = $new->initUploadedFiles();
        $new->uri = $new->initUri();
        $new->stream = $new->initStream();

        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function getServerParams(): array
    {
        return $this->request->server ?: [];
    }

    /**
     * {@inheritDoc}
     */
    public function getCookieParams(): array
    {
        return $this->request->cookie ?: [];
    }

    /**
     * {@inheritDoc}
     */
    public function withCookieParams(array $cookies): self
    {
        if ($cookies) {
            $new = clone $this;
            $new->request->cookie = $cookies;
            return $new;
        }
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getQueryParams(): array
    {
        return $this->request->get ?: [];
    }

    /**
     * {@inheritDoc}
     */
    public function withQueryParams(array $query): self
    {
        if ($query) {
            $new = clone $this;
            $new->request->get = $query;
            return $new;
        }
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles;
    }

    /**
     * {@inheritDoc}
     */
    public function withUploadedFiles(array $uploadedFiles): self
    {
        if ($uploadedFiles) {
            $new = clone $this;
            $new->uploadedFiles = $uploadedFiles;
            return $new;
        }
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getParsedBody()
    {
        return $this->request->post;
    }

    /**
     * {@inheritDoc}
     */
    public function withParsedBody($data): self
    {
        if ($data) {
            $new = clone $this;
            $new->request->post = $data;
            return $new;
        }
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * {@inheritDoc}
     */
    public function getAttribute($name, $default = null)
    {
        if (array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        }

        return $default;
    }

    /**
     * {@inheritDoc}
     */
    public function withAttribute($name, $value): self
    {
        if (array_key_exists($name, $this->attributes) && $this->attributes[$name] === $value) {
            return $this;
        }

        $new = clone $this;
        $new->attributes[$name] = $value;
        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function withoutAttribute($name): self
    {
        if (!array_key_exists($name, $this->attributes)) {
            return $this;
        }

        $new = clone $this;
        unset($new->attributes[$name]);
        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function getRequestTarget(): string
    {
        if ($this->requestTarget !== null) {
            return $this->requestTarget;
        }

        $target = $this->uri->getPath();

        if ($target && $query = $this->uri->getQuery()) {
            $target .= '?' . $query;
        }

        return $target ?: '/';
    }

    /**
     * {@inheritDoc}
     */
    public function withRequestTarget($requestTarget): self
    {
        if ($requestTarget === $this->requestTarget) {
            return $this;
        }

        $new = clone $this;
        $new->requestTarget = $requestTarget;
        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * {@inheritDoc}
     */
    public function withMethod($method): self
    {
        if ($method === $this->method) {
            return $this;
        }

        $new = clone $this;
        $new->method = $method;
        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    /**
     * {@inheritDoc}
     */
    public function withUri(UriInterface $uri, $preserveHost = false): self
    {
        if ($uri === $this->uri) {
            return $this;
        }

        $new = clone $this;
        $new->uri = $uri;

        if (!$preserveHost || !$this->hasHeader('host')) {
            $new->updateHostHeaderFromUri();
        }

        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function getProtocolVersion(): string
    {
        return $this->protocol;
    }

    /**
     * {@inheritDoc}
     */
    public function withProtocolVersion($version): self
    {
        if ($version === $this->protocol) {
            return $this;
        }

        $new = clone $this;
        $new->protocol = $version;
        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function getHeaders(): array
    {
        return $this->request->header ?: [];
    }

    /**
     * {@inheritDoc}
     */
    public function hasHeader($name): bool
    {
        return isset($this->request->header[$this->normalizeHeaderName($name)]);
    }

    /**
     * {@inheritDoc}
     */
    public function getHeader($name): array
    {
        $header = $this->request->header[$this->normalizeHeaderName($name)] ?? [];
        if (is_string($header)) {
            return [$header];
        } else {
            return $header;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getHeaderLine($name): string
    {
        return implode(',', $this->getHeader($name));
    }

    /**
     * {@inheritDoc}
     */
    public function withHeader($name, $value): self
    {
        $name = $this->normalizeHeaderName($name);
        if ($this->request->header[$name] === $value) {
            return $this;
        }

        $new = clone $this;
        $new->request->header[$name] =  $this->normalizeHeaderValue($value);
        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function withAddedHeader($name, $value): self
    {
        if (!$this->hasHeader($name)) {
            return $this->withHeader($name, $value);
        }

        $name = $this->normalizeHeaderName($name);

        $new = clone $this;
        $new->request->header[$name] = array_merge($this->request->header[$name], $this->normalizeHeaderValue($value));
        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function withoutHeader($name): self
    {
        if (!$this->hasHeader($name)) {
            return $this;
        }

        $new = clone $this;
        unset($new->request->header[$this->normalizeHeaderName($name)]);
        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function withBody(StreamInterface $body): self
    {
        $new = clone $this;
        $new->stream = $body;
        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function getBody(): StreamInterface
    {
        return $this->stream;
    }

    private function initProtocolVersion(): string
    {
        if (array_key_exists('server_protocol', $this->request->server) && $this->request->server['server_protocol'] !== '') {
            return str_replace('HTTP/', '', $this->request->server['server_protocol']);
        } else {
            return $this->protocol;
        }
    }

    private function initUri(): UriInterface
    {
        $uri = $this->uriFactory->createUri();

        if (array_key_exists('https', $this->request->server) && $this->request->server['https'] !== '' && $this->request->server['https'] !== 'off') {
            $uri = $uri->withScheme('https');
        } else {
            $uri = $uri->withScheme('http');
        }

        if (isset($this->request->server['http_host'])) {
            if (1 === preg_match('/^(.+):(\d+)$/', $this->request->server['http_host'], $matches)) {
                $uri = $uri->withHost($matches[1])->withPort($matches[2]);
            } else {
                $uri = $uri->withHost($this->request->server['http_host']);
            }
        } elseif (isset($this->request->server['server_name'])) {
            $uri = $uri->withHost($this->request->server['server_name']);
        }

        if (isset($this->request->server['server_port'])) {
            $uri = $uri->withPort($this->request->server['server_port']);
        }

        if (isset($this->request->server['request_uri'])) {
            $uri = $uri->withPath(explode('?', $this->request->server['request_uri'])[0]);
        }

        if (isset($this->request->server['query_string'])) {
            $uri = $uri->withQuery($this->request->server['query_string']);
        }

        return $uri;
    }

    private function initStream(): StreamInterface
    {
        if (strpos($this->request->header['content-type'] ?? '', 'multipart/form-data') !== false) {
            return $this->streamFactory->createStream();
        } else {
            return $this->streamFactory->createStream($this->request->getContent());
        }
    }

    private function initUploadedFiles(): array
    {
        $files = [];
        if ($this->request->files) {
            $this->populateUploadedFiles($this->request->files, $files);
        }
        return $files;
    }

    private function populateUploadedFiles(array $files, array &$result): void
    {
        foreach ($files as $name => $file) {
            $result[$name] = [];
            if (is_array(current($file))) {
                $this->populateUploadedFiles($file, $result[$name]);
            } else {
                try {
                    $stream = $this->streamFactory->createStreamFromFile($file['tmp_name']);
                } catch (RuntimeException $e) {
                    $stream = $this->streamFactory->createStream();
                }

                $result[$name] = $this->uploadedFileFactory->createUploadedFile(
                    $stream,
                    (int) $file['size'],
                    (int) $file['error'],
                    $file['name'],
                    $file['type']
                );
            }
        }
    }

    private function normalizeHeaderName(string $name): string
    {
        return strtolower($name);
    }

    /**
     * @param mixed $value
     */
    private function normalizeHeaderValue($value): array
    {
        return is_array($value) ? $value : [$value];
    }

    private function updateHostHeaderFromUri(): void
    {
        if (!$host = $this->uri->getHost()) {
            return;
        }

        if ($port = $this->uri->getPort()) {
            $host .= ':' . $port;
        }

        $this->request->header['host'] = [$host];
    }
}
