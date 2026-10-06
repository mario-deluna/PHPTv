<?php declare(strict_types = 1);

namespace PHPTv\RemoteControl;

use PHPTv\Exceptions\HttpBadResponseException;
use PHPTv\Exceptions\UnexpectedTvResponseException;

final class Guide extends RemoteControl
{

	protected string $tvApiEndpoint = 'guide';

	/**
	 * @return array<mixed>
	 *
	 * @throws HttpBadResponseException
	 * @throws UnexpectedTvResponseException
	 */
	public function getServiceProtocols(): array
	{
		return $this->request('getServiceProtocols');
	}

}
