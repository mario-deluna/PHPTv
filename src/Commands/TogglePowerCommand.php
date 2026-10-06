<?php declare(strict_types = 1);

namespace PHPTv\Commands;

use League\CLImate\CLImate;
use PHPTv\Contracts\RemoteInterface;
use PHPTv\Exceptions\HttpBadResponseException;
use PHPTv\Exceptions\IrccException;
use PHPTv\Exceptions\UnexpectedTvResponseException;
use PHPTv\RemoteControl\System;

final readonly class TogglePowerCommand extends BaseCommand
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
		try {
			if ($this->system->isOn()) {
				$this->cli->out('Turning the tv Off...');
				$this->remote->send('PowerOff');
			} else {
				$this->cli->out('Turning the tv On...');
				$this->remote->send('TvPower');
			}
		} catch (
			HttpBadResponseException |
			IrccException |
			UnexpectedTvResponseException $e
		) {
			$this->cli->error($e->getMessage());
		}
	}

}
