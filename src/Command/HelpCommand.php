<?php

declare(strict_types=1);

namespace Ep\Command;

use Ep\Console\Command;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Files\FileHelper;
use Yiisoft\Files\PathMatcher\PathMatcher;
use ReflectionClass;
use ReflectionMethod;

final class HelpCommand extends Command
{
    public function indexAction(Aliases $aliases): string
    {
        $commandPath = $aliases->get('@ep/src/Command');
        $files = array_map(static function ($path) use ($commandPath): string {
            return trim(str_replace([$commandPath, '.php'], '', $path), '/');
        }, FileHelper::findFiles($commandPath, [
            'filter' => (new PathMatcher())->only('**Command.php')->except(__FILE__)
        ]));

        foreach ($files as $name) {
            $class = 'Ep\\Command\\' . $name;
            $commands[$name] = array_filter((new ReflectionClass($class))->getMethods(ReflectionMethod::IS_PUBLIC), static fn (ReflectionMethod $ref) => strpos($ref->getName(), 'Action') !== false);
        }

        $help = "\nThe following commands are available:\n";
        foreach ($commands as $name => $actions) {
            $help .= "\n";
            /** @var ReflectionMethod $ref */
            foreach ($actions as $ref) {
                $action = '/' . substr($ref->getName(), 0, strrpos($ref->getName(), 'Action'));
                if ($action === '/index') {
                    $action = '';
                }
                $help .= sprintf("- %s%s %s\n", lcfirst(substr($name, 0, strrpos($name, 'Command'))), $action, $this->getComment($ref));
            }
        }
        return $help;
    }

    private function getComment(ReflectionMethod $ref): string
    {
        $docComment = $ref->getDocComment();
        if ($docComment === false) {
            return '';
        } else {
            preg_match('~/\*\*\s*\* (.*)[\s\S]+\*/~', $docComment, $matches);
            return $matches[1] ?? '';
        }
    }
}
