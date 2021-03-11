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

    public function isPost(): bool
    {
        return $this->request->getMethod() === Method::POST;
    }

    public function isAjax(): bool
    {
        return ($this->request->getServerParams()['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
    }

    public function getCurrentUrl(string $path = '', array $params = []): string
    {
        $uri = $this->request->getUri();
        if ($path || $params) {
            $url = '';
            if (($scheme = $uri->getScheme()) !== '') {
                $url .= $scheme . ':';
            }
            if (($authority = $uri->getAuthority()) !== '') {
                $url .= '//' . $authority;
            }
            if ($path || ($path = $uri->getPath()) !== '') {
                $url .= $path;
            }
            if ($params) {
                $url .= '?' . http_build_query($params);
            } elseif (($query = $uri->getQuery()) !== '') {
                $url .= '?' . $query;
            }
            return $url;
        } else {
            return $uri->__toString();
        }
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
            $new = clone $this;
            $new->request = $new->request->withCookieParams($cookies);
            return $new;
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
            $new = clone $this;
            $new->request = $this->request->withQueryParams($query);
            return $new;
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
            $new = clone $this;
            $new->request = $new->request->withUploadedFiles($uploadedFiles);
            return $new;
        }
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getParsedBody()
    {
        return $this->request->getParsedBody() ?: [];
    }

    /**
     * {@inheritDoc}
     */
    public function withParsedBody($data): self
    {
        if ($data) {
            $new = clone $this;
            $new->request = $new->request->withParsedBody($data);
            return $new;
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
        $new = clone $this;
        $new->request = $new->request->withAttribute($name, $value);
        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function withoutAttribute($name): self
    {
        $new = clone $this;
        $new->request = $new->request->withoutAttribute($name);
        return $new;
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
        $new = clone $this;
        $new->request = $new->request->withRequestTarget($requestTarget);
        return $new;
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
        $new = clone $this;
        $new->request = $new->request->withMethod($method);
        return $new;
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
        $new = clone $this;
        $new->request = $new->request->withUri($uri, $preserveHost);
        return $new;
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
        $new = clone $this;
        $new->request = $new->request->withProtocolVersion($version);
        return $new;
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
        $new = clone $this;
        $new->request = $new->request->withHeader($name, $value);
        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function withAddedHeader($name, $value): self
    {
        $new = clone $this;
        $new->request = $new->request->withAddedHeader($name, $value);
        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function withoutHeader($name): self
    {
        $new = clone $this;
        $new->request = $new->request->withoutHeader($name);
        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function withBody(StreamInterface $body): self
    {
        $new = clone $this;
        $new->request = $new->request->withBody($body);
        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function getBody(): StreamInterface
    {
        return $this->request->getBody();
    }
}
