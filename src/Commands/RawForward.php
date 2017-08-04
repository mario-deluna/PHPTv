<?php 

namespace PHPTv\Commands;

use PHPTv\BaseCommand;
use PHPTv\Exception;

class RawForward extends BaseCommand
{
	/** 
     * Execute the command
     * 
     * @return void
     */
	public function execute()
	{	
		$ircc = $this->repo('IRCC');
		$ircc->refreshAvailableCommands();

		$this->cli->blue('Available commands:');
		$this->cli->columns(array_keys($ircc->getAvailableCommands()));

		$this->cli->br();
		$this->cli->out('Simply enter ":e" to go back to the remote.');

		$input = $this->cli->input('> ');

		while (1) 
		{
			// go back if wished
			$inputString = $input->prompt();
			if ($inputString === ':e') {
				$this->cli->yellow('Ok.'); return;
			}

			// skip empty
			if ($inputString === '') {
				continue;
			}

			try {
				$ircc->send(ucfirst($inputString));
			} catch(Exception $e)  {
				$this->cli->error($e->getMessage());
			}
		}
	}
}