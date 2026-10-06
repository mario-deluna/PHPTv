<?php declare(strict_types = 1);

namespace PHPTv\Commands;

use ClanCats\Container\Container;
use League\CLImate\CLImate;
use PHPTv\Commands\Help\CommandHelpCommand;
use function array_column;
use function array_filter;
use function array_shift;
use function array_unique;
use function array_values;
use function explode;
use function readline;
use function readline_add_history;
use function readline_clear_history;
use function readline_completion_function;
use function sort;
use function stripos;
use function trim;

final class ExecuteCommand extends BaseCommand
{

    /**
     * @var array<string>
     */
    protected array $readlineCommandHistory = [];

    public function __construct(
        private Container $container,
        private CommandHelpCommand $helpCommand,
        CLImate $cli,
    )
    {
        parent::__construct($cli);

        $this->readlineCommandHistory = array_column($helpCommand->keyMapping, 'command');
        sort($this->readlineCommandHistory);
    }

    /** @param array<string> $args */
    public function execute(array $args = []): void
    {
        $this->cli->out('Please enter the command you wish to execute. Enter "help" to list the available commands. Press ctrl+d to exit.');

        $this->readlinePrepare();

        readline_completion_function(function (string $input, int $index): array {
            if ($input === '') {
                return array_unique(array_filter($this->readlineCommandHistory));
            }

            $matches = [];
            foreach ($this->readlineCommandHistory as $command) {
                if (stripos($command, $input) === 0) {
                    $matches[] = $command;
                }
            }
            return $matches;
        });

        $commandString = trim((string) $this->readlinePromt('> '));

        $commandParts = explode(' ', $commandString);
        $commandName = array_shift($commandParts);
        $commandArgs = array_values(array_filter($commandParts));

        if ($commandName === '') {
            return;
        }

        if ($commandName === 'help') {
            $this->helpCommand->execute();
            $this->execute();
            return;
        }

        if ($commandName === 'execute') {
            $this->cli->error('You are one of the funny kind huh?');
            return;
        }

        if (!$this->container->has('cmd.' . $commandName)) {
            $this->cli->error('There is no command with the name "' . $commandName . '" available.');
            return;
        }

        $this->readlineAddHistory($commandString);

        /** @var BaseCommand $cmd */
        $cmd = $this->container->get('cmd.' . $commandName);
        $cmd->execute($commandArgs);
    }

    protected function readlineAddHistory(string $command): void
    {
        $this->readlineCommandHistory[] = $command;
    }

    protected function readlinePromt(string $prefix): string|false
    {
        return readline($prefix);
    }

    protected function readlinePrepare(): void
    {
        readline_clear_history();

        foreach ($this->readlineCommandHistory as $command) {
            readline_add_history($command);
        }
    }

}
