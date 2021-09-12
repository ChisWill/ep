<?php

declare(strict_types=1);

namespace Ep\Command\Service;

use Ep\Kit\Annotate;
use Psr\Container\ContainerInterface;

final class ScanService extends Service
{
    private Annotate $annotate;

    public function __construct(
        ContainerInterface $container,
        Annotate $annotate
    ) {
        parent::__construct($container);

        $this->annotate = $annotate;
    }

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
    }

    public function scan(): void
    {
        $classList = $this->util->getClassList($this->userRootNamespace, $this->request->getOption('ignore'));

        $this->consoleService->progress(fn ($progressBar) => $this->annotate->cache($classList, static fn () => $progressBar->advance()), count($classList));

        $this->consoleService->writeln();
    }
}
