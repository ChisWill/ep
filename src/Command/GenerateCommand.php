<?php

declare(strict_types=1);

namespace Ep\Command;

use Ep\Command\Service\GenerateService;
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
        if ($this->isMultiple($params['table'] ?? '')) {
            foreach ($this->getPieces($params['table']) as $table) {
                $params['table'] = $table;
                $result[] = $this->singleModel($params);
            }
            return implode(PHP_EOL, $result);
        } else {
            return $this->singleModel($params);
        }
    }

    private function isMultiple(string $param): bool
    {
        return strpos($param, ',') !== false;
    }

    private function getPieces(string $param): array
    {
        return explode(',', $param);
    }

    private function singleModel(array $params): string
    {
        try {
            $this->service->initModel($params);

            if ($this->service->hasModel()) {
                return $this->service->updateModel();
            } else {
                return $this->service->createModel();
            }
        } catch (Throwable $t) {
            return $t->getMessage();
        }
    }
}
