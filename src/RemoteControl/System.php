<?php declare(strict_types = 1);

namespace PHPTv\RemoteControl;

use PHPTv\Crate\InterfaceInformationCrate;
use PHPTv\Enum\PowerStatus;
use PHPTv\Exceptions\HttpBadResponseException;
use PHPTv\Exceptions\UnexpectedTvResponseException;

final class System extends RemoteControl
{

	protected string $tvApiEndpoint = 'system';

	/**
	 * @throws HttpBadResponseException
	 * @throws UnexpectedTvResponseException
	 */
	public function getInterfaceInformation(): InterfaceInformationCrate
	{
		return InterfaceInformationCrate::fromArray($this->request('getInterfaceInformation')[0]);
	}

	/**
	 * @throws HttpBadResponseException
	 * @throws UnexpectedTvResponseException
	 */
	public function getPowerStatus(): PowerStatus
	{
		return PowerStatus::from($this->request('getPowerStatus')[0]['status']);
	}

	/**
	 * @throws HttpBadResponseException
	 * @throws UnexpectedTvResponseException
	 */
	public function isOn(): bool
	{
		return $this->getPowerStatus() === PowerStatus::Active;
	}

}
