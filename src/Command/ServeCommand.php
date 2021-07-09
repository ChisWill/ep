<?php

declare(strict_types=1);

namespace Ep\Command;

use Ep\Command\Service\ServeService;
use Ep\Console\Command;
use Ep\Contract\ConsoleRequestInterface;
use Ep\Contract\ConsoleResponseInterface;
use Symfony\Component\Console\Input\InputOption;

final class ServeCommand extends Command
{
    private ServeService $service;

    public function __construct(ServeService $service)
    {
        $this->service = $service;

        $this->setDefinition('index', [
            new InputOption('address', null, InputOption::VALUE_REQUIRED, 'Host to serve at'),
            new InputOption('port', null, InputOption::VALUE_REQUIRED, 'Port to serve at'),
            new InputOption('docroot', null, InputOption::VALUE_REQUIRED, 'Document root to serve from'),
            new InputOption('router', null, InputOption::VALUE_REQUIRED, 'Path to router script')
        ])
            ->setDescription('Runs PHP built-in web server');
    }

    public function indexAction(ConsoleRequestInterface $request): ConsoleResponseInterface
    {
        $this->service->initialize($request->getOptions());

        $this->service->serve();

        return $this->success();
    }
}
