<?php declare(strict_types = 1);

namespace PHPTv\RemoteControl;

use PHPTv\Http\Client;

abstract class RemoteControl
{

	protected string $tvApiEndpoint = '';

	public function __construct(private Client $client)
	{
	}

	/**
	 * @param array<mixed> $params
	 * @return array<mixed>
	 */
	public function request(
		string $method,
		array $params = [],
		string $version = '1.0',
	): array
	{
		return $this->client->requestMethod($this->tvApiEndpoint, $method, $params, $version);
	}

}
