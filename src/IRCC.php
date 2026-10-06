<?php declare(strict_types = 1);

namespace PHPTv;

use PHPTv\Contracts\RemoteInterface;
use PHPTv\Crate\IrccNameValueCrate;
use PHPTv\Exceptions\HttpAuthException;
use PHPTv\Exceptions\HttpBadResponseException;
use PHPTv\Exceptions\IrccException;
use PHPTv\Exceptions\LogicException;
use PHPTv\Exceptions\UnexpectedTvResponseException;
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

	public function refreshAvailableCommands(): void
	{
		try {
			$response = $this->client->requestMethod('system', 'getRemoteControllerInfo');
		} catch (HttpBadResponseException | UnexpectedTvResponseException $e) {
			return;
		}

		$this->availableCommands = [];

		foreach ($response[1] as $item) {
			$command = IrccNameValueCrate::fromArray($item);
			$this->availableCommands[$command->name] = $command->value;
		}
	}

	/**
	 * First step of IRCC registration. Returns true if already authenticated
	 * (cookie received), false if PIN is required (TV shows PIN on screen).
	 *
	 * @throws HttpBadResponseException
	 */
	public function startAuthentication(): bool
	{
		$cookie = $this->client->actRegister($this->makeHashId(), $this->appName);

		if ($cookie !== null) {
			$this->client->setAuthCookie($cookie);
			return true;
		}

		return false;
	}

	/**
	 * Second step: confirm registration with PIN shown on TV screen.
	 *
	 * @throws HttpBadResponseException
	 * @throws LogicException
	 */
	public function confirmAuthentication(string $pin): void
	{
		$cookie = $this->client->actRegister($this->makeHashId(), $this->appName, $pin);

		if ($cookie === null) {
			throw new LogicException('IRCC authentication failed: invalid PIN.');
		}

		$this->client->setAuthCookie($cookie);
	}

	/**
	 * @throws HttpBadResponseException
	 * @throws IrccException
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
		} catch (HttpAuthException $e) {
			if (!$this->startAuthentication()) {
				throw IrccException::sessionExpiredPinRequired();
			}

			try {
				$this->client->sendIRCC($this->availableCommands[$codeName]);
			} catch (HttpAuthException $e) {
				throw IrccException::reAuthenticationFailed($e);
			}
		}
	}

	private function makeHashId(): string
	{
		return md5($this->appName . ':1');
	}

}
