<?php

declare(strict_types=1);

namespace Ep\Command;

use Ep\Console\Command;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Files\FileHelper;
use Yiisoft\Files\PathMatcher\PathMatcher;
use ReflectionClass;
use ReflectionMethod;

final class ListCommand extends Command
{
    public function indexAction(Aliases $aliases): int
    {
        $commandPath = str_replace('\\', '/', $aliases->get('@ep/src/Command'));
        $files = array_map(static function ($path) use ($commandPath): string {
            return trim(str_replace([$commandPath, '.php'], '', $path), '/');
        }, FileHelper::findFiles($commandPath, [
            'filter' => (new PathMatcher())->only('**Command.php')->except(str_replace('\\', '/', __FILE__))
        ]));

        $commands = $this->getCommands($files);
        $commandMaxLength = $this->getCommandMaxLength($commands);

        $help = "\nThe following commands are available:\n";
        $lastCommand = null;
        foreach ($commands as $row) {
            if (($name = $this->getCommandName($row['command'])) != $lastCommand) {
                $lastCommand = $name;
                $help .= "\n";
            }
            $help .= sprintf("- %s%s%s\n", $row['command'], str_repeat(' ', $commandMaxLength - strlen($row['command']) + 1), $row['desc']);
        }

        return $this->success($help);
    }

    private function getCommandName(string $command): string
    {
        if (strpos($command, '/') !== false) {
            return explode('/', $command)[0];
        } else {
            return $command;
        }
    }

    private function getCommands(array $files): array
    {
        foreach ($files as $name) {
            $class = 'Ep\\Command\\' . $name;
            $map[$name] = array_filter((new ReflectionClass($class))->getMethods(ReflectionMethod::IS_PUBLIC), static fn (ReflectionMethod $ref) => strpos($ref->getName(), 'Action') !== false);
        }
        foreach ($map as $name => $actions) {
            foreach ($actions as $ref) {
                $action = '/' . substr($ref->getName(), 0, strrpos($ref->getName(), 'Action'));
                if ($action === '/index') {
                    $action = '';
                }
                $commands[] = [
                    'command' => lcfirst(substr($name, 0, strrpos($name, 'Command'))) . $action,
                    'desc' => $this->getComment($ref)
                ];
            }
        }
        return $commands;
    }

    private function getCommandMaxLength(array $commands): int
    {
        $maxLength = 0;
        foreach ($commands as $row) {
            $length = strlen($row['command']);
            if ($length > $maxLength) {
                $maxLength = $length;
            }
        }
        return $maxLength;
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
