<?php

declare(strict_types=1);

namespace Ep\Console;

use Ep\Contract\ConsoleFactoryInterface;
use Ep\Contract\ConsoleResponseInterface;
use Symfony\Component\Console\Helper\HelperInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

final class Service
{
    private Application $application;
    private InputInterface $input;
    private OutputInterface $output;
    private ConsoleFactoryInterface $factory;

    public function __construct(
        Application $application,
        InputInterface $input,
        OutputInterface $output,
        ConsoleFactoryInterface $factory
    ) {
        $this->application = $application;
        $this->input = $input;
        $this->output = $output;
        $this->factory = $factory;
    }

    public function withInput(InputInterface $input): self
    {
        $new = clone $this;
        $new->input = $input;
        return $new;
    }

    public function withOutput(OutputInterface $output): self
    {
        $new = clone $this;
        $new->output = $output;
        return $new;
    }

    public function status(int $code): ConsoleResponseInterface
    {
        return $this->factory
            ->createResponse($this->output)
            ->setCode($code);
    }

    public function write(string $message = '', int $options = 0): void
    {
        $this->output->write($message, false, $options);
    }

    public function writeln(string $message = '', int $options = 0): void
    {
        $this->output->writeln($message, $options);
    }

    public function confirm(string $message, bool $default = false): bool
    {
        /** @var QuestionHelper */
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion($message . ' [<comment>' . ($default ? 'Yes' : 'No') . '</>] ', $default);
        return $helper->ask($this->input, $this->output, $question);
    }

    public function prompt(string $message, string $default = '', bool $hidden = false): string
    {
        /** @var QuestionHelper */
        $helper = $this->getHelper('question');
        $question = new Question($message, $default);
        $question->setHidden($hidden);
        return $helper->ask($this->input, $this->output, $question);
    }

    public function renderTable(array $headers, array $rows): void
    {
        (new Table($this->output))
            ->setHeaders($headers)
            ->setRows($rows)
            ->render();
    }

    public function progress(callable $callback, int $max = 100): void
    {
        $progress = new ProgressBar($this->output, $max);

        $progress->start();

        call_user_func($callback, $progress);

        $progress->finish();
    }

    public function getHelper(string $name): HelperInterface
    {
        return $this->application
            ->getHelperSet()
            ->get($name);
    }

    public function call(string $command, array $arguments = []): int
    {
        return $this->application
            ->find($command)
            ->run(
                new ArrayInput(compact('command') + $arguments),
                $this->output
            );
    }
}
