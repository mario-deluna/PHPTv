<?php declare(strict_types = 1);

namespace PHPTv\Commands\Help;

use PHPTv\Commands\BaseCommand;

final class RemoteHelpCommand extends BaseCommand
{

    /**
     * @var array<int, array<string, string>>
     */
    protected array $keyMapping = [
        ['key' => '←', 'action' => 'Left', 'description' => 'Navigate left'],
        ['key' => '→', 'action' => 'Right', 'description' => 'Navigate Right'],
        ['key' => '↑', 'action' => 'Up', 'description' => 'Navigate Up'],
        ['key' => '↓', 'action' => 'Down', 'description' => 'Navigate Down'],
        ['key' => '↵', 'action' => 'Confirm', 'description' => 'Enter / Confirm'],
        ['key' => '⌫', 'action' => 'Return', 'description' => 'Go Back / Return'],
        ['key' => 'c', 'action' => 'Command', 'description' => 'Opens the command prompt.'],
        ['key' => 'f', 'action' => 'Forward', 'description' => 'Enter the forward raw commands mode.'],
        ['key' => 'g', 'action' => 'Home', 'description' => 'Go Home.'],
        ['key' => 'p', 'action' => 'TogglePower', 'description' => 'Turns the TV on / Off'],
        ['key' => 'm', 'action' => 'Mute', 'description' => 'Mute / Unmute the Tv.'],
        ['key' => 'b', 'action' => 'VolumeDown', 'description' => 'Turn down for what?'],
        ['key' => 'n', 'action' => 'VolumeUp', 'description' => 'Turn up the Volume'],
    ];

    /** @param array<string> $args */
    public function execute(array $args = []): void
    {
        $this->cli->table($this->keyMapping);
    }

}
