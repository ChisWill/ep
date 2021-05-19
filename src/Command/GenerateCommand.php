<?php

declare(strict_types=1);

namespace Ep\Command;

use Ep\Console\Command;
use Ep\Contract\ConsoleRequestInterface;
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

    /**
     * 生成模型
     */
    public function modelAction(ConsoleRequestInterface $request): string
    {
        $params = $request->getParams();
        if ($this->service->isMultiple($params['table'] ?? '')) {
            foreach ($this->service->getPieces($params['table']) as $table) {
                $params['table'] = $table;
                $result[] = $this->singleModel($params);
            }
            return implode(PHP_EOL, $result);
        } else {
            return $this->singleModel($params);
        }
    }

    private function singleModel(array $params): string
    {
        try {
            $this->service->validateModel($params);
        } catch (Throwable $t) {
            return $t->getMessage();
        }

        if ($this->service->hasModel()) {
            return $this->service->updateModel();
        } else {
            return $this->service->createModel(
                $this->getView()->renderPartial('model', [
                    'namespace' => $this->service->getNamespace(),
                    'primaryKey' => $this->service->getPrimaryKey(),
                    'tableName' => $this->service->getTableName(),
                    'className' => $this->service->getClassName(),
                    'property' => $this->service->getProperty(),
                    'rules' => $this->service->getRules()
                ])
            );
        }
    }

    public function getViewPath(): string
    {
        return '@ep/views';
    }
}
