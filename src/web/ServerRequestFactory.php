<?php

declare(strict_types=1);

namespace Ep\Web;

use RuntimeException;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

final class ServerRequestFactory
{
    private ServerRequestFactoryInterface $serverRequestFactory;
    private UriFactoryInterface $uriFactory;
    private UploadedFileFactoryInterface $uploadedFileFactory;
    private StreamFactoryInterface $streamFactory;

    public function __construct(
        ServerRequestFactoryInterface $serverRequestFactory,
        UriFactoryInterface $uriFactory,
        UploadedFileFactoryInterface $uploadedFileFactory,
        StreamFactoryInterface $streamFactory
    ) {
        $this->serverRequestFactory = $serverRequestFactory;
        $this->uriFactory = $uriFactory;
        $this->uploadedFileFactory = $uploadedFileFactory;
        $this->streamFactory = $streamFactory;
    }

    public function createFromGlobals(): ServerRequestInterface
    {
        return $this->createFromParameters(
            $_SERVER,
            $this->getHeadersFromGlobals(),
            $_COOKIE,
            $_GET,
            $_POST,
            $_FILES,
            fopen('php://input', 'rb') ?: null
        );
    }

    /**
     * @param array $server
     * @param array $headers
     * @param array $cookies
     * @param array $get
     * @param array $post
     * @param array $files
     * @param resource|StreamInterface|string|null $body
     *
     * @return ServerRequestInterface
     */
    public function createFromParameters(array $server, array $headers = [], array $cookies = [], array $get = [], array $post = [], array $files = [], $body = null): ServerRequestInterface
    {
        $method = $server['REQUEST_METHOD'] ?? null;
        if ($method === null) {
            throw new RuntimeException('Unable to determine HTTP request method.');
        }

        $uri = $this->getUri($server);

        $request = $this->serverRequestFactory->createServerRequest($method, $uri, $server);

        foreach ($headers as $name => $value) {
            $request = $request->withAddedHeader($name, $value);
        }

        $protocol = '1.1';
        if (array_key_exists('SERVER_PROTOCOL', $server) && $server['SERVER_PROTOCOL'] !== '') {
            $protocol = str_replace('HTTP/', '', $server['SERVER_PROTOCOL']);
        }

        $request = $request
            ->withProtocolVersion($protocol)
            ->withQueryParams($get)
            ->withParsedBody($post)
            ->withCookieParams($cookies)
            ->withUploadedFiles($this->getUploadedFilesArray($files));

        if ($body === null) {
            return $request;
        }

        if (is_resource($body)) {
            $body = $this->streamFactory->createStreamFromResource($body);
        } elseif (is_string($body)) {
            $body = $this->streamFactory->createStream($body);
        } elseif (!$body instanceof StreamInterface) {
            throw new InvalidArgumentException('Body parameter for ServerRequestFactory::createFromParameters() must be instance of StreamInterface, resource or null.');
        }

        return $request->withBody($body);
    }

    private function getHeadersFromGlobals(): array
    {
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            if ($headers === false) {
                $headers = [];
            }
        } else {
            $headers = [];
            foreach ($_SERVER as $name => $value) {
                if (strncmp($name, 'HTTP_', 5) === 0) {
                    $name = str_replace(' ', '-', strtolower(str_replace('_', ' ', substr($name, 5))));
                    $headers[$name] = $value;
                }
            }
        }

        return $headers;
    }

    private function getUri(array $server): UriInterface
    {
        $uri = $this->uriFactory->createUri();

        if (array_key_exists('HTTPS', $server) && $server['HTTPS'] !== '' && $server['HTTPS'] !== 'off') {
            $uri = $uri->withScheme('https');
        } else {
            $uri = $uri->withScheme('http');
        }

        if (isset($server['HTTP_HOST'])) {
            if (1 === preg_match('/^(.+):(\d+)$/', $server['HTTP_HOST'], $matches)) {
                $uri = $uri->withHost($matches[1])->withPort($matches[2]);
            } else {
                $uri = $uri->withHost($server['HTTP_HOST']);
            }
        } elseif (isset($server['SERVER_NAME'])) {
            $uri = $uri->withHost($server['SERVER_NAME']);
        }

        if (isset($server['SERVER_PORT'])) {
            $uri = $uri->withPort($server['SERVER_PORT']);
        }

        if (isset($server['REQUEST_URI'])) {
            $uri = $uri->withPath(\explode('?', $server['REQUEST_URI'])[0]);
        }

        if (isset($server['QUERY_STRING'])) {
            $uri = $uri->withQuery($server['QUERY_STRING']);
        }

        return $uri;
    }

    private function getUploadedFilesArray(array $filesArray): array
    {
        $files = [];
        foreach ($filesArray as $class => $info) {
            $files[$class] = [];
            $this->populateUploadedFileRecursive($files[$class], $info['name'], $info['tmp_name'], $info['type'], $info['size'], $info['error']);
        }
        return $files;
    }

    /**
     * Populates uploaded files array from $_FILE data structure recursively.
     *
     * @param array $files uploaded files array to be populated.
     * @param mixed $names file names provided by PHP
     * @param mixed $tempNames temporary file names provided by PHP
     * @param mixed $types file types provided by PHP
     * @param mixed $sizes file sizes provided by PHP
     * @param mixed $errors uploading issues provided by PHP
     */
    private function populateUploadedFileRecursive(array &$files, $names, $tempNames, $types, $sizes, $errors): void
    {
        if (is_array($names)) {
            foreach ($names as $i => $name) {
                $files[$i] = [];
                $this->populateUploadedFileRecursive($files[$i], $name, $tempNames[$i], $types[$i], $sizes[$i], $errors[$i]);
            }
        } else {
            try {
                $stream = $this->streamFactory->createStreamFromFile($tempNames);
            } catch (RuntimeException $e) {
                $stream = $this->streamFactory->createStream();
            }

            $files = $this->uploadedFileFactory->createUploadedFile(
                $stream,
                (int)$sizes,
                (int)$errors,
                $names,
                $types
            );
        }
    }
}
