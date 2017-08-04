<?php 

namespace PHPTv\Commands;

use PHPTv\BaseCommand;
use PHPTv\Exception;

class ExecuteCommand extends BaseCommand
{
	/** 
     * Execute the command
     * 
     * @return void
     */
	public function execute()
	{	
		$this->cli->out('Please enter the command you wish to execute. Enter "help" to list the available commands.');

		$input = $this->cli->input('> ');
		$commandName = $input->prompt();

		// check for help
		if ($commandName === 'help')
		{
			$this->container->get('cmd.help.command')->execute();
			$this->execute();
			return;
		}

		// check for recursion
		if ($commandName === 'execute')
		{
			$this->cli->error('You are one of the funny kind huh?'); 
			return;
		}

		if (!$this->container->has('cmd.' . $commandName))
		{
			$this->cli->error('There is no command with the name "' . $commandName . '" available.'); 
			return;
		}

		$cmd = $this->container->get('cmd.' . $commandName);
		$cmd->execute();
	}
}