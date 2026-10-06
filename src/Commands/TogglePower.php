<?php declare(strict_types = 1);

namespace PHPTv\Commands;

use League\CLImate\CLImate;
use PHPTv\IRCC;
use PHPTv\RemoteControl\System;

final class TogglePower extends BaseCommand
{

    public function __construct(
        private IRCC $ircc,
        private System $system,
        CLImate $cli,
    )
    {
        parent::__construct($cli);
    }

    /** @param array<string> $args */
    public function execute(array $args = []): void
    {
        if ($this->system->isOn()) {
            $this->cli->out('Turning the tv Off...');
            $this->ircc->send('PowerOff');
        } else {
            $this->cli->out('Turning the tv On...');
            $this->ircc->send('TvPower');
        }
    }

}
