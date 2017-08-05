<?php 

namespace PHPTv;

class IRCC 
{
    use ClientAwareTrait;

    /**
     * The Command mapping
     * 
     * @var array
     */
    protected $availableCommands = [];

    public function getAvailableCommands() : array
    {
        return $this->availableCommands;
    }

    /**
     * Load the remote infos from the TV Api
     * 
     * @return bool returns true on success
     */
    public function refreshAvailableCommands()
    {
        try {
            $response = $this->client->requestMethod('system', 'getRemoteControllerInfo')[1];
        } 
        catch(Exception $e) { return false; }

        // reset current commands
        $this->availableCommands = [];

        foreach ($response as $command) 
        {
            $this->availableCommands[$command['name']] = $command['value'];
        }

        return true;
    }

    /**
     * Send an IRCC by name
     * 
     * @param string            $codeName
     */
    public function send(string $codeName) 
    {
        if (!isset($this->availableCommands[$codeName])) {
            throw new Exception('Invalid command "'. $codeName .'" given. It does not seem to be supported by the TV. Try refreshing the available commands.');
        }

        return $this->client->sendIRCC($this->availableCommands[$codeName]);
    }
}