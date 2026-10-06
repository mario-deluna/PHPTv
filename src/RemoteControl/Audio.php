<?php declare(strict_types = 1);

namespace PHPTv\RemoteControl;

use PHPTv\Exceptions\LogicException;

final class Audio extends RemoteControl
{

	protected string $tvApiEndpoint = 'audio';

	/**
	 * @return array<mixed>
	 *
	 * @throws LogicException
	 */
	public function getVolumeInformation(): array
	{
		return $this->request('getVolumeInformation')[0] ?? [];
	}

	/**
	 * @return array<mixed>
	 *
	 * @throws LogicException
	 */
	public function setAudioVolume(
		string $volume,
		string $target = 'speaker',
	): array
	{
		return $this->request('setAudioVolume', [[
			'volume' => $volume,
			'target' => $target,
		]]);
	}

}
