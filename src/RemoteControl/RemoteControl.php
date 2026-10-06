<?php declare(strict_types = 1);

namespace PHPTv\RemoteControl;

use PHPTv\Exceptions\HttpBadResponseException;
use PHPTv\Exceptions\LogicException;
use PHPTv\Exceptions\UnexpectedTvResponseException;
use PHPTv\Http\Client;

abstract readonly class RemoteControl
{

	public function __construct(private Client $client)
	{
	}

	/**
	 * @param array<string, string> $params
	 * @return array<mixed>
	 *
	 * @throws HttpBadResponseException
	 * @throws UnexpectedTvResponseException
	 */
	protected function request(
		string $method,
		array $params = [],
		string $version = '1.0',
	): array
	{
		return $this->client->requestMethod($this->getTvApiEndpoint(), $method, $params, $version);
	}

	protected function getTvApiEndpoint(): string
	{
		throw new LogicException('Let\'s getTvApiEndpoint() do not yet implemented');
	}

}
