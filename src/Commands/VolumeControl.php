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

		

		var_dump(); return;

		$ircc = $this->repo('IRCC');

		// turn tv off
		if ($this->repo('system')->isOn()) 
		{
			$this->cli->out('Turning the tv Off...');
			$ircc->send('PowerOff');
		} 
		// turn on
		else 
		{
			$this->cli->out('Turning the tv On...');
			$ircc->send('TvPower');
		}
	}

	protected function printVolumeInformation()
	{
		$this->cli->table($this->repo('audio')->getVolumeInformation());
	}
}