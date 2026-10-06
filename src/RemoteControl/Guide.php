<?php declare(strict_types = 1);

namespace PHPTv\RemoteControl;

use PHPTv\Crate\ServiceProtocolCrate;
use PHPTv\Exceptions\HttpBadResponseException;
use PHPTv\Exceptions\UnexpectedTvResponseException;

final class Guide extends RemoteControl
{

	protected string $tvApiEndpoint = 'guide';

	/**
	 * @return list<ServiceProtocolCrate>
	 *
	 * @throws HttpBadResponseException
	 * @throws UnexpectedTvResponseException
	 */
	public function getServiceProtocols(): array
	{
		$result = [];

		foreach ($this->request('getServiceProtocols') as $item) {
			$result[] = ServiceProtocolCrate::fromArray($item);
		}

		return $result;
	}

}
