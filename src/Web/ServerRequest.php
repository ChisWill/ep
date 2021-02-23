<?php

declare(strict_types=1);

namespace Ep\Web;

use Yiisoft\Http\Method;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

final class ServerRequest implements ServerRequestInterface
{
    private ServerRequestInterface $request;

    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * {@inheritDoc}
     */
    public function isPost(): bool
    {
        return $this->request->getMethod() === Method::POST;
    }

    /**
     * {@inheritDoc}
     */
    public function isAjax(): bool
    {
        return ($this->request->getServerParams()['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
    }

    /**
     * {@inheritDoc}
     */
    public function getServerParams(): array
    {
        return $this->request->getServerParams();
    }

    /**
     * {@inheritDoc}
     */
    public function getCookieParams(): array
    {
        return $this->request->getCookieParams();
    }

    /**
     * {@inheritDoc}
     */
    public function withCookieParams(array $cookies): self
    {
        if ($cookies) {
            $this->request = $this->request->withCookieParams($cookies);
        }
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getQueryParams(): array
    {
        return $this->request->getQueryParams();
    }

    /**
     * {@inheritDoc}
     */
    public function withQueryParams(array $query): self
    {
        if ($query) {
            $this->request = $this->request->withQueryParams($query);
        }
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getUploadedFiles(): array
    {
        return $this->request->getUploadedFiles();
    }

    /**
     * {@inheritDoc}
     */
    public function withUploadedFiles(array $uploadedFiles): self
    {
        if ($uploadedFiles) {
            $this->request = $this->request->withUploadedFiles($uploadedFiles);
        }
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getParsedBody()
    {
        return $this->request->getParsedBody();
    }

    /**
     * {@inheritDoc}
     */
    public function withParsedBody($data): self
    {
        if ($data) {
            $this->request = $this->request->withParsedBody($data);
        }
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getAttributes(): array
    {
        return $this->request->getAttributes();
    }

    /**
     * {@inheritDoc}
     */
    public function getAttribute($name, $default = null)
    {
        return $this->request->getAttribute($name, $default);
    }

    /**
     * {@inheritDoc}
     */
    public function withAttribute($name, $value): self
    {
        $this->request = $this->request->withAttribute($name, $value);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function withoutAttribute($name): self
    {
        $this->request = $this->request->withoutAttribute($name);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getRequestTarget(): string
    {
        return $this->request->getRequestTarget();
    }

    /**
     * {@inheritDoc}
     */
    public function withRequestTarget($requestTarget): self
    {
        $this->request = $this->request->withRequestTarget($requestTarget);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getMethod(): string
    {
        return $this->request->getMethod();
    }

    /**
     * {@inheritDoc}
     */
    public function withMethod($method): self
    {
        $this->request = $this->request->withMethod($method);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getUri(): UriInterface
    {
        return $this->request->getUri();
    }

    /**
     * {@inheritDoc}
     */
    public function withUri(UriInterface $uri, $preserveHost = false): self
    {
        $this->request = $this->request->withUri($uri, $preserveHost);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getProtocolVersion(): string
    {
        return $this->request->getProtocolVersion();
    }

    /**
     * {@inheritDoc}
     */
    public function withProtocolVersion($version): self
    {
        $this->request = $this->request->withProtocolVersion($version);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getHeaders(): array
    {
        return $this->request->getHeaders();
    }

    /**
     * {@inheritDoc}
     */
    public function hasHeader($name): bool
    {
        return $this->request->hasHeader($name);
    }

    /**
     * {@inheritDoc}
     */
    public function getHeader($name): array
    {
        return $this->request->getHeader($name);
    }

    /**
     * {@inheritDoc}
     */
    public function getHeaderLine($name): string
    {
        return $this->request->getHeaderLine($name);
    }

    /**
     * {@inheritDoc}
     */
    public function withHeader($name, $value): self
    {
        $this->request = $this->request->withHeader($name, $value);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function withAddedHeader($name, $value): self
    {
        $this->request = $this->request->withAddedHeader($name, $value);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function withoutHeader($name): self
    {
        $this->request = $this->request->withoutHeader($name);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function withBody(StreamInterface $body): self
    {
        $this->request = $this->request->withBody($body);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getBody(): StreamInterface
    {
        return $this->request->getBody();
    }
}
