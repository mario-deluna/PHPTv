<?php declare(strict_types = 1);

namespace PHPTv\RemoteControl;

final class Audio extends RemoteControl
{

    protected string $tvApiEndpoint = 'audio';

    /** @return array<mixed> */
    public function getVolumeInformation(): array
    {
        return $this->request('getVolumeInformation')[0] ?? [];
    }

    /** @return array<mixed> */
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
