<?php

declare(strict_types=1);

namespace Ep\Tests\App\Command;

use Ep;
use Ep\Annotation\Aspect;
use Ep\Console\Command;
use Ep\Console\CommandDefinition;
use Ep\Console\Service;
use Ep\Contract\ConsoleRequestInterface;
use Ep\Contract\ConsoleResponseInterface;
use Ep\Tests\App\Aspect\ConsoleAspect;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

class InitCommand extends Command
{
    private Service $service;

    public function __construct(Service $service)
    {
        $this->service = $service;

        $this
            ->createDefinition('index')
            ->addArgument('name', null, 'your name')
            ->addOption('type', 't', InputOption::VALUE_NONE);
    }

    public function before(ConsoleRequestInterface $request, ConsoleResponseInterface $response)
    {
        $this->getService()->writeln('command before');
        return true;
    }

    public function after(ConsoleRequestInterface $request, ConsoleResponseInterface $response): ConsoleResponseInterface
    {
        $this->getService()->writeln('command after');
        return $response;
    }

    /**
     * @Aspect(ConsoleAspect::class)
     */
    public function indexAction(ConsoleRequestInterface $request)
    {
        $message = 'Welcome Basic, ' . $request->getArgument('name');

        echo 'show over';

        return $this->success($message);
    }

    public function logAction(LoggerInterface $logger)
    {
        $logger->info('log info', ['act' => self::class]);

        return $this->success();
    }

    public function requestAction(ConsoleRequestInterface $request)
    {
        t([
            'route' => $request->getRoute(),
            'options' => $request->getOptions(),
            'argvs' => $request->getArguments()
        ]);

        return $this->success();
    }

    public function callAction(ConsoleRequestInterface $request)
    {
        $this->service->call('init/table');

        return $this->success('call over');
    }

    public function tableAction()
    {
        $this->service->renderTable([
            'name', 'id', 'age'
        ], [
            ['zs', 1, 33],
            ['fe', 31, 333],
            ['gvb', 51, 315],
        ]);

        return $this->success('table over');
    }

    public function progressAction()
    {
        $this->service->progress(function (ProgressBar $bar): void {
            $i = 0;
            while ($i++ < 50) {
                $bar->advance(2);
                usleep(30 * 1000);
            }
        });

        return $this->success();
    }

    public function echoArrAction()
    {
        $message = 'con';
        return $this->success(json_encode(compact('message')));
    }
}
