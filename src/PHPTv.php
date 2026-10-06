<?php declare(strict_types = 1);

namespace PHPTv;

use ClanCats\Container\Container;
use League\CLImate\CLImate;
use PHPTv\Commands\BaseCommand;
use PHPTv\Crate\ServiceProtocolCrate;
use PHPTv\Exceptions\HttpBadResponseException;
use PHPTv\Exceptions\IrccException;
use PHPTv\Exceptions\LogicException;
use PHPTv\Exceptions\UnexpectedTvResponseException;
use PHPTv\RemoteControl\Guide;
use PHPTv\RemoteControl\System;
use function bin2hex;
use function count;
use function fread;
use function get_object_vars;
use function readline;
use function system;
use function trim;
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

	/**
	 * @param array<string> $args
	 */
	public function execute(array $args = []): void
	{
		throw new LogicException('This command should only be executed using "connect".');
	}

	public function connect(): void
	{
		$this->cli->out('Connecting to ' . $this->container->getParameter('baseUrl') . '...');

		try {
			$protocols = $this->guide->getServiceProtocols();
		} catch (HttpBadResponseException | UnexpectedTvResponseException $e) {
			$this->cli->error('Could not connect to the TV. ' . $e->getMessage());
			return;
		}

		$this->cli->info('Success!');
		$this->cli->br();

		$this->displayAvailableServiceProtocols($protocols);
		$this->showDeviceInfos();
		$this->authenticateIRCC();
		$this->startCMDLoop();
	}

	/**
	 * @param list<ServiceProtocolCrate> $protocols
	 */
	private function displayAvailableServiceProtocols(array $protocols): void
	{
		$this->cli->blue(count($protocols) . ' service protocols are available: ');

		$protocolNames = [];
		foreach ($protocols as $protocol) {
			$protocolNames[] = $protocol->name;
		}

		$this->cli->columns($protocolNames);
	}

	private function showDeviceInfos(): void
	{
		$this->cli->br();
		$this->cli->blue('TV Interface:');

		try {
			$information = $this->system->getInterfaceInformation();
			$this->cli->columns(get_object_vars($information));
		} catch (HttpBadResponseException | UnexpectedTvResponseException $e) {
			$this->cli->error('Could not load device info: ' . $e->getMessage());
		}
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

	private function authenticateIRCC(): void
	{
		$this->cli->br();

		try {
			if ($this->ircc->startAuthentication()) {
				$this->cli->info('IRCC ready.');
				return;
			}
		} catch (HttpBadResponseException $e) {
			$this->cli->error('IRCC authentication failed: ' . $e->getMessage());
			$this->cli->yellow('Key presses will not work until the TV is reachable and authenticated.');
			return;
		}

		$this->cli->yellow('A PIN has been displayed on your TV screen.');
		system('stty sane');
		$pin = trim((string) readline('Enter PIN: '));
		system('stty -echo -icanon');

		try {
			$this->ircc->confirmAuthentication($pin);
			$this->cli->info('IRCC authenticated.');
		} catch (HttpBadResponseException | LogicException $e) {
			$this->cli->error('IRCC authentication failed: ' . $e->getMessage());
			$this->cli->yellow('Key presses will not work until the TV is reachable and authenticated.');
		}
	}

	private function startCMDLoop(): void
	{
		$this->ircc->refreshAvailableCommands();

		$this->cli->br();
		$this->cli->out('<green>Ready!</green> You can now press the arrow keys to navigate your tv. Press "h" to view the keymapping.');

		$this->enterRemoteControlMode();

		while ($c = fread(STDIN, 3)) {
			try {
				match ($c) {
					"\x1b" => $this->ircc->send('Return'), // Escape
					"\x1b[A" => $this->ircc->send('Up'), // arrow up
					"\x1b[B" => $this->ircc->send('Down'), // arrow down
					"\x1b[C" => $this->ircc->send('Right'), // arrow right
					"\x1b[D" => $this->ircc->send('Left'), // arrow left
					"\n" => $this->ircc->send('Confirm'), // Enter
					"\x7f" => $this->ircc->send('Return'), // Backspace
					'c' => $this->executeCommand('execute'),
					'f' => $this->executeCommand('raw_forward'),
					'g' => $this->ircc->send('Home'),
					'h' => $this->executeCommand('help.remote'),
					'i' => $this->executeCommand('input_menu'),
					'p' => $this->executeCommand('toggle_power'),
					'm' => $this->ircc->send('Mute'),
					'n' => $this->ircc->send('VolumeUp'),
					'b' => $this->ircc->send('VolumeDown'),
					default => $this->cli->error('Unknown Command... (' . bin2hex($c) . ')'),
				};
			} catch (HttpBadResponseException | IrccException $e) {
				$this->cli->error($e->getMessage());
			}
		}
	}

}
