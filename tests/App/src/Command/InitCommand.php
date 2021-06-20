<?php

declare(strict_types=1);

namespace Ep\Tests\App\Command;

use Ep;
use Ep\Annotation\Aspect;
use Ep\Console\Command;
use Ep\Console\CommandDefinition;
use Ep\Console\ConsoleRequest;
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

        $this->setDefinition('index', [
            new InputArgument('name', null, 'your name'),
            new InputOption('type', 't', InputOption::VALUE_NONE)
        ]);
    }

    public function before(ConsoleRequestInterface $request)
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
        $c = Ep::getDi()->get(CurlCommand::class);
        $request->setArgument('action', 'aspect');
        $r = new ConsoleRequest(new ArrayInput([
            'action' => 'string'
        ]));
        /** @var CommandDefinition */
        $inputD = $c->getDefinitions()['single'];
        $input = new ArrayInput([
            'action' => 'string'
        ], new InputDefinition($inputD->getDefinition()));

        $c->singleAction($r);

        return $this->success();
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

        return $this->success();
    }

    public function progressAction()
    {
        $this->service->progress(function (ProgressBar $bar) {
            $i = 0;
            while ($i++ < 50) {
                $bar->advance(2);
                usleep(30 * 1000);
            }
        });

        return $this->success();
    }
}
