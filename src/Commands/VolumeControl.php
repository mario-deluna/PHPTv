<?php 

namespace PHPTv\Commands;

use PHPTv\BaseCommand;
use PHPTv\Exception;

class VolumeControl extends BaseCommand
{
    /** 
     * Execute the command
     * 
     * @return void
     */
    public function execute(array $args = [])
    {   
        if (!isset($args[0]))
        {
            return $this->printVolumeInformation();
        }

        // get the target volume
        $targetVolume = $args[0];

        $target = 'speaker';
        if (isset($args[1])) {
            $target = 'headphone';
        }

        $this->cli->out('setting volume to: '. $targetVolume . ' on ' . $target);

        var_dump($this->repo('audio')->setAudioVolume($targetVolume, $target));
    }

    protected function printVolumeInformation()
    {
        $this->cli->table($this->repo('audio')->getVolumeInformation());
        $this->cli->out("You can set the volume between 0 and 100.\n - volume 40 # Sets the volume to 40\n - volume 40 -a # Sets the volume on the speakers and headphones.");
    }
}