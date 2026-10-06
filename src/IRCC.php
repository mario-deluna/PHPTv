<?php declare(strict_types = 1);

namespace PHPTv;

use PHPTv\Exceptions\LogicException;
use PHPTv\Http\Client;

final class IRCC
{

	/**
	 * @var array<string, string>
	 */
	private array $availableCommands = [];

	public function __construct(private Client $client)
	{
	}

	/**
	 * @return array<string, string>
	 */
	public function getAvailableCommands(): array
	{
		return $this->availableCommands;
	}

	public function refreshAvailableCommands(): bool
	{
		try {
			$response = $this->client->requestMethod('system', 'getRemoteControllerInfo')[1];
		} catch (LogicException $e) {
			return false;
		}

		$this->availableCommands = [];

		foreach ($response as $command) {
			$this->availableCommands[(string) $command['name']] = (string) $command['value'];
		}

		return true;
	}

	/**
	 * @throws LogicException
	 */
	public function send(string $codeName): string
	{
		if (!isset($this->availableCommands[$codeName])) {
			throw new LogicException(
				'Invalid command "' . $codeName . '" given. It does not seem to be supported by the TV. Try refreshing the available commands.',
			);
		}

		return $this->client->sendIRCC($this->availableCommands[$codeName]);
	}

}
