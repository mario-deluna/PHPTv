<?php declare(strict_types = 1);

namespace PHPTv\RemoteControl;

final class Guide extends RemoteControl
{

    protected string $tvApiEndpoint = 'guide';

    /** @return array<mixed> */
    public function getServiceProtocols(): array
    {
        return $this->request('getServiceProtocols');
    }

}
