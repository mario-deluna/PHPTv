<?php declare(strict_types = 1);

namespace PHPTv\RemoteControl;

use PHPTv\Exceptions\HttpBadResponseException;
use PHPTv\Exceptions\UnexpectedTvResponseException;

final class System extends RemoteControl
{

	protected string $tvApiEndpoint = 'system';

	/**
	 * @return array<mixed>
	 *
	 * @throws HttpBadResponseException
	 * @throws UnexpectedTvResponseException
	 */
	public function getInterfaceInformation(): array
	{
		return $this->request('getInterfaceInformation');
	}

	/**
	 * @throws HttpBadResponseException
	 * @throws UnexpectedTvResponseException
	 */
	public function getPowerStatus(): string
	{
		return $this->request('getPowerStatus')[0]['status'] ?? 'unknown';
	}

	/**
	 * @throws HttpBadResponseException
	 * @throws UnexpectedTvResponseException
	 */
	public function isOn(): bool
	{
		return $this->getPowerStatus() === 'active';
	}

}
