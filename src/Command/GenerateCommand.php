<?php

declare(strict_types=1);

namespace Ep\Command;

use Ep\Command\Service\GenerateService;
use Ep\Console\Command;
use Ep\Contract\ConsoleRequestInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Throwable;

final class GenerateCommand extends Command
{
    private GenerateService $service;

    public function __construct(GenerateService $service)
    {
        $this->service = $service;
    }

    public function modelDefinition(): array
    {
        return [
            new InputArgument('table', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'The table name'),
            new InputOption('db', null, InputArgument::OPTIONAL, 'The db name'),
            new InputOption('path', null, InputArgument::OPTIONAL, 'The path of model'),
            new InputOption('prefix', null, InputArgument::OPTIONAL, 'The prefix of table'),
        ];
    }

    /**
     * 生成模型
     */
    public function modelAction(ConsoleRequestInterface $request): int
    {
        $tables = $request->getArgument('table');
        if (count($tables) === 1) {
            $request->setOption('table', $tables[0]);
            return $this->singleModel($request->getOptions());
        } else {
            foreach ($tables as $table) {
                $request->setOption('table', $table);
                $this->singleModel($request->getOptions());
            }
            return Command::OK;
        }
    }

    private function singleModel(array $options): int
    {
        try {
            $this->service->initModel($options);

            if ($this->service->hasModel()) {
                $this->service->updateModel();
            } else {
                $this->service->createModel();
            }
            return Command::OK;
        } catch (Throwable $t) {
            return $this->string($t->getMessage());
        }
    }
}
