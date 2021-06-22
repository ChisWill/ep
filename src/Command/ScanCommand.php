<?php

declare(strict_types=1);

namespace Ep\Command;

use Ep\Command\Service\ScanService;
use Ep\Console\Command;
use Ep\Contract\ConsoleRequestInterface;
use Ep\Contract\ConsoleResponseInterface;

final class ScanCommand extends Command
{
    private ScanService $service;

    public function __construct(ScanService $service)
    {
        $this->service = $service;

        $this->setDefinition('annotation')->setDescription('Scan root path to generate annotation cache');
    }

    public function annotationAction(ConsoleRequestInterface $request): ConsoleResponseInterface
    {
        $this->service->init($request->getOptions());

        $this->service->annotation();

        return $this->success();
    }
}
