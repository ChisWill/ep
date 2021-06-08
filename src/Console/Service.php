<?php

declare(strict_types=1);

namespace Ep\Console;

use Ep\Contract\ConsoleRequestInterface;
use Ep\Contract\ConsoleResponseInterface;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Helper\HelperInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

final class Service
{
    private SymfonyApplication $symfonyApplication;
    private ConsoleRequestInterface $request;
    private ConsoleResponseInterface $response;

    public function __construct(
        SymfonyApplication $symfonyApplication,
        ConsoleRequestInterface $request,
        ConsoleResponseInterface $response
    ) {
        $this->symfonyApplication = $symfonyApplication;
        $this->request = $request;
        $this->response = $response;
    }

    public function getRequest(): ConsoleRequestInterface
    {
        return $this->request;
    }

    public function getResponse(): ConsoleResponseInterface
    {
        return $this->response;
    }

    public function write(string $message = '', int $options = 0): void
    {
        $this->response->write($message, $options);
    }

    public function writeln(string $message = '', int $options = 0): void
    {
        $this->response->writeln($message, $options);
    }

    public function confirm(string $message, bool $default = false): bool
    {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion($message . '[y/n] ', $default);
        return $helper->ask($this->request->getInput(), $this->response->getOutput(), $question);
    }

    public function prompt(string $message, string $default = '', bool $hidden = false): string
    {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $question = new Question($message, $default);
        if ($hidden) {
            $question->setHidden(true);
        }
        return $helper->ask($this->request->getInput(), $this->response->getOutput(), $question);
    }

    public function renderTable(array $headers, array $rows): void
    {
        $helper = new Table($this->response->getOutput());

        $helper
            ->setHeaders($headers)
            ->setRows($rows)
            ->render();
    }

    public function progress(callable $callback, int $max = 100): void
    {
        $progress = new ProgressBar($this->response->getOutput(), $max);

        $progress->start();

        call_user_func($callback, $progress);

        $progress->finish();
    }

    public function getHelper(string $name): HelperInterface
    {
        return $this->symfonyApplication->getHelperSet()->get($name);
    }
}
