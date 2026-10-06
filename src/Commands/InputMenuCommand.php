<?php declare(strict_types = 1);

namespace PHPTv\Commands;

use League\CLImate\CLImate;
use PHPTv\Contracts\RemoteInterface;
use PHPTv\Exceptions\IrccException;

final readonly class InputMenuCommand extends BaseCommand
{

	public function __construct(
		private RemoteInterface $remote,
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
			$this->remote->send('Input');
		} catch (IrccException $e) {
			$this->cli->error($e->getMessage());
		}
	}

}
