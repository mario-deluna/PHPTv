<?php declare(strict_types = 1);

namespace PHPTv\Commands\Help;

use PHPTv\Commands\BaseCommand;

final readonly class CommandHelpCommand extends BaseCommand
{

	/**
	 * @var array<int, array<string, string>>
	 */
	public const KEY_MAPPING = [
		[
			'command' => 'raw_forward',
			'description' => 'Send raw commands via IRCC.',
		],
		[
			'command' => 'toggle_power',
			'description' => 'Toggle the TV on / off.',
		],
		[
			'command' => 'volume',
			'description' => 'Volume control',
		],
		[
			'command' => 'input_menu',
			'description' => 'Open native input selection on TV (key: i)',
		],
	];

	/**
	 * @param array<string> $args
	 */
	public function execute(array $args = []): void
	{
		$this->cli->table(self::KEY_MAPPING);
	}

}
