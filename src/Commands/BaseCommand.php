<?php declare(strict_types = 1);

namespace PHPTv\Commands;

use League\CLImate\CLImate;

abstract class BaseCommand
{

    public function __construct(protected CLImate $cli)
    {
    }

    /** @param array<string> $args */
    abstract public function execute(array $args = []): void;

}
