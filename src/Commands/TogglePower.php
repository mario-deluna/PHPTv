<?php declare(strict_types = 1);

namespace PHPTv\Commands;

use League\CLImate\CLImate;
use PHPTv\Contracts\RemoteInterface;
use PHPTv\RemoteControl\System;

final class TogglePower extends BaseCommand
{

	public function __construct(
		private RemoteInterface $remote,
		private System $system,
		CLImate $cli,
	)
	{
		parent::__construct($cli);
	}

	/**
	 * @param array<string> $args
	 */
	public function execute(array $args = []): void
	{
		if ($this->system->isOn()) {
			$this->cli->out('Turning the tv Off...');
			$this->remote->send('PowerOff');
		} else {
			$this->cli->out('Turning the tv On...');
			$this->remote->send('TvPower');
		}
	}

}
