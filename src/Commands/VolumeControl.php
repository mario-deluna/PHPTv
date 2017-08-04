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

		$this->cli->out('setting volume to: '. $targetVolume);

		var_dump($this->repo('audio')->setAudioVolume($targetVolume));
	}

	protected function printVolumeInformation()
	{
		$this->cli->table($this->repo('audio')->getVolumeInformation());
	}
}