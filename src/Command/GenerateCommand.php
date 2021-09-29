<?php

declare(strict_types=1);

namespace Ep\Command;

use Ep\Command\Service\GenerateService;
use Ep\Console\Command;
use Ep\Contract\ConsoleRequestInterface;
use Ep\Contract\ConsoleResponseInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

final class GenerateCommand extends Command
{
    private GenerateService $service;

    public function __construct(GenerateService $service)
    {
        $this->service = $service;

        $this
            ->createDefinition('model')
            ->addArgument('table', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'Table name')
            ->addOption('app', 'a', InputOption::VALUE_REQUIRED, 'App name')
            ->addOption('db', null, InputOption::VALUE_REQUIRED, 'Db name')
            ->addOption('path', null, InputOption::VALUE_REQUIRED, 'Save path')
            ->addOption('prefix', null, InputOption::VALUE_REQUIRED, 'Table prefix')
            ->setDescription('Generate model');
    }

    public function keyAction(ConsoleRequestInterface $request): ConsoleResponseInterface
    {
        $this->service
            ->load($request)
            ->createKey();

        return $this->success();
    }

    public function modelAction(ConsoleRequestInterface $request): ConsoleResponseInterface
    {
        foreach ($request->getArgument('table') as $table) {
            $request->setOption('table', $table);
            $this->generateModel($request);
        }
        return $this->success();
    }

    private function generateModel(ConsoleRequestInterface $request): void
    {
        $service = $this->service->load($request);

        if ($service->hasModel()) {
            $service->updateModel();
        } else {
            $service->createModel();
        }
    }
}
