<?php declare(strict_types = 1);

namespace PHPTv;

use PHPTv\Contracts\RemoteInterface;
use PHPTv\Exceptions\AuthException;
use PHPTv\Exceptions\LogicException;
use PHPTv\Http\Client;
use function md5;

final class IRCC implements RemoteInterface
{

	/**
	 * @var array<string, string>
	 */
	private array $availableCommands = [];

	public function __construct(
		private Client $client,
		private string $appName = 'PHPTv',
	)
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
	 * First step of IRCC registration. Returns true if already authenticated
	 * (cookie received), false if PIN is required (TV shows PIN on screen).
	 *
	 * @throws LogicException
	 */
	public function startAuthentication(): bool
	{
		$cookie = $this->client->actRegister(md5($this->appName . ':1'), $this->appName);

		if ($cookie !== null) {
			$this->client->setAuthCookie($cookie);
			return true;
		}

		return false;
	}

	/**
	 * Second step: confirm registration with PIN shown on TV screen.
	 *
	 * @throws LogicException
	 */
	public function confirmAuthentication(string $pin): void
	{
		$cookie = $this->client->actRegister($this->appName . ':1', $this->appName, $pin);

		if ($cookie === null) {
			throw new LogicException('IRCC authentication failed: invalid PIN.');
		}

		$this->client->setAuthCookie($cookie);
	}

	/**
	 * @throws LogicException
	 */
	public function send(string $codeName): void
	{
		if (!isset($this->availableCommands[$codeName])) {
			throw new LogicException(
				'Invalid command "' . $codeName . '" given. It does not seem to be supported by the TV. Try refreshing the available commands.',
			);
		}

		try {
			$this->client->sendIRCC($this->availableCommands[$codeName]);
		} catch (AuthException $e) {
			if (!$this->startAuthentication()) {
				throw new LogicException('IRCC session expired and re-authentication requires a PIN. Restart the app.');
			}

			try {
				$this->client->sendIRCC($this->availableCommands[$codeName]);
			} catch (AuthException $e) {
				throw new LogicException('IRCC re-authentication succeeded but command still failed.', 0, $e);
			}
		}
	}

}
