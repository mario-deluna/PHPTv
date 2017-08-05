<?php 

namespace PHPTv;

class System extends Repository
{
    /**
     * The current used endpoint string 
     * 
     * @var string
     */
    protected $tvApiEndpoint = 'system';

    /**
     * Get information about the tv
     */
    public function getInterfaceInformation() : array
    {
        return $this->request('getInterfaceInformation');
    }

    /**
     * Is the tv On / Off
     */
    public function getPowerStatus() : string
    {
        return $this->request('getPowerStatus')[0]['status'] ?? 'unknown';
    }

    /**
     * Is the tv On / Off
     */
    public function isOn() : bool
    {
        return $this->getPowerStatus() === 'active';
    }
}