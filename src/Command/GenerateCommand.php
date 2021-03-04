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

        $data = [
            'namespace' => $this->service->getNamespace(),
            'primaryKey' => $this->service->getPrimaryKey(),
            'tableName' => $this->service->getTableName(),
            'className' => $this->service->getClassName(),
            'columns' => $this->service->getColumns(),
            'rules' => $this->service->getRules(),
            'typecast' => $this->service->typecast()
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
