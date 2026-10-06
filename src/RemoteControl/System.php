<?php declare(strict_types = 1);

namespace PHPTv\RemoteControl;

use PHPTv\Exceptions\LogicException;

final class System extends RemoteControl
{

	protected string $tvApiEndpoint = 'system';

	/**
	 * @return array<mixed>
	 *
	 * @throws LogicException
	 */
	public function getInterfaceInformation(): array
	{
		return $this->request('getInterfaceInformation');
	}

	/**
	 * @throws LogicException
	 */
	public function getPowerStatus(): string
	{
		return $this->request('getPowerStatus')[0]['status'] ?? 'unknown';
	}

	/**
	 * @throws LogicException
	 */
	public function isOn(): bool
	{
		return $this->getPowerStatus() === 'active';
	}

}
