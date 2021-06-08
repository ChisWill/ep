<?php

declare(strict_types=1);

namespace Ep\Console;

use Ep\Contract\ConsoleRequestInterface;
use Ep\Contract\ConsoleResponseInterface;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Helper\HelperInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
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

    private function getHelper(string $name): HelperInterface
    {
        return $this->symfonyApplication->getHelperSet()->get($name);
    }
}
