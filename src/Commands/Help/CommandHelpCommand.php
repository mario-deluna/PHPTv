<?php declare(strict_types = 1);

namespace PHPTv\Commands\Help;

use PHPTv\Commands\BaseCommand;

final class CommandHelpCommand extends BaseCommand
{

    /**
     * @var array<int, array<string, string>>
     */
    public array $keyMapping = [
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
    ];

    /** @param array<string> $args */
    public function execute(array $args = []): void
    {
        $this->cli->table($this->keyMapping);
    }

}
