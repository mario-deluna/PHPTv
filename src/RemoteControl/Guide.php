<?php declare(strict_types = 1);

namespace PHPTv\RemoteControl;

use PHPTv\Exceptions\LogicException;

final class Guide extends RemoteControl
{

	protected string $tvApiEndpoint = 'guide';

	/**
	 * @return array<mixed>
	 *
	 * @throws LogicException
	 */
	public function getServiceProtocols(): array
	{
		return $this->request('getServiceProtocols');
	}

}
