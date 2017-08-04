<?php 

namespace PHPTv\Commands;

use ClanCats\Container\Container;
use League\Climate\Climate;

use PHPTv\ReadlineSupportTrait;
use PHPTv\BaseCommand;
use PHPTv\Exception;

class ExecuteCommand extends BaseCommand
{	
	use ReadlineSupportTrait;

	/**
	 * Construct every command with the container and command line interface
	 * 
	 * @param Container 			$container
	 * @param Climate 				$cli
	 */
	public function __construct(Container $container, Climate $cli)
	{
		parent::__construct($container, $cli);

		// first fill the command history with available commands
		$this->readlineCommandHistory = array_column($container->get('cmd.help.command')->keyMapping, 'command');

		// inital sort the command history
		sort($this->readlineCommandHistory);
	}
	/** 
     * Execute the command
     * 
     * @return void
     */
	public function execute(array $args = [])
	{	
		$this->cli->out('Please enter the command you wish to execute. Enter "help" to list the available commands.');

		// always clear the history and rebuild
		// this allows us to have diffrent histories per command
		$this->readlinePrepare();

		// read the command 
		$commandString = $this->readlinePromt('> ');

		// split arguments 
		$commandString = explode(' ', $commandString);
		$commandName = array_shift($commandString);
		$commandArgs = $commandString;

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

		// update the current history
		$this->readlineAddHistory($commandName);

		// execute
		$cmd = $this->container->get('cmd.' . $commandName);
		$cmd->execute($commandArgs);
	}
}