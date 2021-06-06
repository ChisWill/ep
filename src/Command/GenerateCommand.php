<?php

declare(strict_types=1);

namespace Ep\Command;

use Ep\Command\Service\GenerateService;
use Ep\Console\Command;
use Ep\Contract\ConsoleRequestInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

final class GenerateCommand extends Command
{
    private GenerateService $service;

    public function __construct(GenerateService $service)
    {
        $this->service = $service;

        $this->setDefinition('model', [
            new InputArgument('table', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'The table name'),
            new InputOption('db', null, InputOption::VALUE_REQUIRED, 'The db name'),
            new InputOption('path', null, InputOption::VALUE_REQUIRED, 'The path of model'),
            new InputOption('prefix', null, InputOption::VALUE_REQUIRED, 'The prefix of table')
        ])
            ->setDescription('Generate model');
    }

    /**
     * 生成模型
     */
    public function modelAction(ConsoleRequestInterface $request): int
    {
        foreach ($request->getArgument('table') as $table) {
            $request->setOption('table', $table);
            $this->singleModel($request->getOptions());
        }
        return $this->success();
    }

    private function singleModel(array $options): int
    {
        $this->service->initModel($options);

        if ($this->service->hasModel()) {
            return $this->success($this->service->updateModel());
        } else {
            return $this->success($this->service->createModel());
        }
    }
}
