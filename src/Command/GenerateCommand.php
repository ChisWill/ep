<?php

declare(strict_types=1);

namespace Ep\Command;

use Ep\Console\Command;
use Ep\Console\ConsoleRequest;
use Throwable;

final class GenerateCommand extends Command
{
    private GenerateService $service;

    public function __construct(GenerateService $service)
    {
        $this->service = $service;
    }

    protected function alias(): array
    {
        return [
            'table' => 1
        ];
    }

    public function modelAction(ConsoleRequest $request): string
    {
        try {
            $this->service->validateModel($request->getParams());
        } catch (Throwable $t) {
            return $t->getMessage();
        }

        if ($this->service->hasModel()) {
            return $this->service->updateModel();
        }

        $data = [
            'namespace' => $this->service->getNamespace(),
            'primaryKey' => $this->service->getPrimaryKey(),
            'tableName' => $this->service->getTableName(),
            'className' => $this->service->getClassName(),
            'property' => $this->service->getProperty(),
            'rules' => $this->service->getRules()
        ];

        return $this->service->createModel(
            $this->getView()->renderPartial('model', $data)
        );
    }

    public function getViewPath(): string
    {
        return '@ep/views';
    }
}
