<?php

declare(strict_types=1);

namespace Ep\Command;

use Ep\Console\Command;
use Ep\Console\ConsoleRequest;

final class GenerateCommand extends Command
{
    private GenerateService $service;

    public function __construct(GenerateService $service)
    {
        $this->service = $service;
    }

    public function modelAction(ConsoleRequest $request): string
    {
        $result = $this->service->validateModel($request->getParams());
        if ($result !== true) {
            return $result;
        }

        $data = [
            'namespace' => $this->service->getNamespace(),
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
