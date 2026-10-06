<?php declare(strict_types = 1);

namespace PHPTv\RemoteControl;

use PHPTv\Exceptions\HttpBadResponseException;
use PHPTv\Exceptions\UnexpectedTvResponseException;

final readonly class Audio extends RemoteControl
{

	/**
	 * @return array<mixed>
	 *
	 * @throws HttpBadResponseException
	 * @throws UnexpectedTvResponseException
	 */
	public function getVolumeInformation(): array
	{
		return $this->request('getVolumeInformation')[0] ?? [];
	}

	/**
	 * @throws HttpBadResponseException
	 * @throws UnexpectedTvResponseException
	 */
	public function setAudioVolume(
		string $volume,
		string $target = 'speaker',
	): void
	{
		$this->request('setAudioVolume', [
			'volume' => $volume,
			'target' => $target,
		]);
	}

	protected function getTvApiEndpoint(): string
	{
		return 'audio';
	}

}
