<?php declare(strict_types = 1);

namespace PHPTv\Commands;

use League\CLImate\CLImate;
use PHPTv\Contracts\RemoteInterface;
use PHPTv\Exceptions\LogicException;
use function array_keys;
use function ucfirst;

final class RawForward extends BaseCommand
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
		$this->remote->refreshAvailableCommands();

		$this->cli->blue('Available commands:');
		$this->cli->columns(array_keys($this->remote->getAvailableCommands()));

		$this->cli->br();
		$this->cli->out('Simply enter ":e" to go back to the remote.');

		$input = $this->cli->input('> ');

		while (1) {
			$inputString = $input->prompt();

			if ($inputString === ':e') {
				$this->cli->yellow('Ok.');
				return;
			}

			if ($inputString === '') {
				continue;
			}

			try {
				$this->remote->send(ucfirst($inputString));
			} catch (LogicException $e) {
				$this->cli->error($e->getMessage());
			}
		}
	}

}
