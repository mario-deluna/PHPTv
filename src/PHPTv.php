<?php declare(strict_types = 1);

namespace PHPTv;

use ClanCats\Container\Container;
use League\CLImate\CLImate;
use PHPTv\Commands\BaseCommand;
use PHPTv\Exceptions\LogicException;
use PHPTv\RemoteControl\Guide;
use PHPTv\RemoteControl\System;
use function count;
use function fread;
use function ord;
use function system;
use const STDIN;

final class PHPTv extends BaseCommand
{

    public function __construct(
        private Guide $guide,
        private System $system,
        private IRCC $ircc,
        private Container $container,
        CLImate $cli,
    )
    {
        parent::__construct($cli);
    }

    /** @param array<string> $args */
    public function execute(array $args = []): void
    {
        throw new LogicException('This command should only be executed using "connect".');
    }

    public function connect(): void
    {
        $this->cli->out('Connecting to ' . $this->container->getParameter('baseUrl') . '...');

        try {
            $protocols = $this->guide->getServiceProtocols();
        } catch (LogicException $e) {
            $this->cli->error('Could not connect to the TV. ' . $e->getMessage());
            return;
        }

        $this->cli->info('Success!');
        $this->cli->br();

        $this->displayAvailableServiceProtocols($protocols);
        $this->showDeviceInfos();
        $this->startCMDLoop();
    }

    /** @param array<mixed> $protocols */
    private function displayAvailableServiceProtocols(array $protocols): void
    {
        $this->cli->blue(count($protocols) . ' service protocols are available: ');

        $protocolNames = [];
        foreach ($protocols as $p) {
            $protocolNames[] = $p[0];
        }

        $this->cli->columns($protocolNames);
    }

    private function showDeviceInfos(): void
    {
        $this->cli->br();
        $this->cli->blue('TV Interface:');
        $infos = $this->system->getInterfaceInformation();
        $this->cli->columns($infos[0]);
    }

    private function executeCommand(string $name): void
    {
        $this->cli->br();

        if (!$this->container->has('cmd.' . $name)) {
            $this->cli->error('There is no command with the name ' . $name . ' available.');
            return;
        }

        system('stty sane');

        /** @var BaseCommand $cmd */
        $cmd = $this->container->get('cmd.' . $name);
        $cmd->execute();

        $this->enterRemoteControlMode();
    }

    private function enterRemoteControlMode(): void
    {
        $this->cli->br();
        $this->cli->inline('<blue>[Remote] </blue>');
        system('stty -echo -icanon');
    }

    private function startCMDLoop(): void
    {
        $ircc = $this->ircc;
        $ircc->refreshAvailableCommands();

        $this->cli->br();
        $this->cli->out('<green>Ready!</green> You can now press the arrow keys to navigate your tv. Press "h" to view the keymapping.');

        $this->enterRemoteControlMode();

        while ($c = fread(STDIN, 1)) {
            $key = ord($c);

            switch ($key) {
                // ignore the key prefix
                case 27:
                case 91:
                    break;

                case 68:
                    $ircc->send('Left');
                    break;
                case 67:
                    $ircc->send('Right');
                    break;
                case 65:
                    $ircc->send('Up');
                    break;
                case 66:
                    $ircc->send('Down');
                    break;
                case 10:
                    $ircc->send('Confirm');
                    break;
                case 127:
                    $ircc->send('Return');
                    break;

                // c
                case 99:
                    $this->executeCommand('execute');
                    break;
                // f
                case 102:
                    $this->executeCommand('raw_forward');
                    break;
                // g
                case 103:
                    $ircc->send('Home');
                    break;
                // h
                case 104:
                    $this->executeCommand('help.remote');
                    break;
                // p
                case 112:
                    $this->executeCommand('toggle_power');
                    break;
                // m
                case 109:
                    $ircc->send('Mute');
                    break;
                // n
                case 110:
                    $ircc->send('VolumeUp');
                    break;
                // b
                case 98:
                    $ircc->send('VolumeDown');
                    break;

                default:
                    $this->cli->error("Unknown Command... ($key)");
                    break;
            }
        }
    }

}
