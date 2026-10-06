<?php declare(strict_types = 1);

namespace PHPTv\RemoteControl;

final class System extends RemoteControl
{

    protected string $tvApiEndpoint = 'system';

    /** @return array<mixed> */
    public function getInterfaceInformation(): array
    {
        return $this->request('getInterfaceInformation');
    }

    public function getPowerStatus(): string
    {
        return $this->request('getPowerStatus')[0]['status'] ?? 'unknown';
    }

    public function isOn(): bool
    {
        return $this->getPowerStatus() === 'active';
    }

}
