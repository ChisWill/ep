<?php

declare(strict_types=1);

namespace Ep\Command\Service;

final class ServeService extends Service
{
    private string $address;
    private string $port;
    private string $docroot;
    private string $router;

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this->address = $this->request->getOption('address') ?? $this->defaultOptions['address'] ?? 'localhost';
        $this->port = $this->request->getOption('port') ?? $this->defaultOptions['port'] ?? '8080';
        $this->docroot = $this->request->getOption('docroot') ?? $this->defaultOptions['docroot'] ?? 'public';
        $this->router = $this->request->getOption('router') ?? $this->defaultOptions['router'] ?? '';
    }

    public function serve(): void
    {
        if ($this->isAddressTaken()) {
            $this->error("http://{$this->address}:{$this->port} is taken by another process.");
        }
        if (!$this->isDocrootExists()) {
            $this->error("Document root \"{$this->docroot}\" does not exist.");
        }
        if (!$this->isRouterExists()) {
            $this->error("Routing file \"{$this->router}\" does not exist.");
        }

        passthru('"' . PHP_BINARY . '"' . " -S {$this->address}:{$this->port} -t \"{$this->docroot}\" $this->router");
    }

    private function isAddressTaken(): bool
    {
        $fp = @fsockopen($this->address, (int) $this->port, $errno, $errstr, 1);
        if ($fp === false) {
            return false;
        }
        fclose($fp);
        return true;
    }

    private function isDocrootExists(): bool
    {
        return is_dir($this->docroot);
    }

    private function isRouterExists(): bool
    {
        if ($this->router) {
            return file_exists($this->router);
        }
        return true;
    }
}
