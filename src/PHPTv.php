<?php 

namespace PHPTv;

use ClanCats\Container\Container;
use League\Climate\Climate;

class PHPTv extends BaseCommand
{
	/** 
     * Execute the command
     * 
     * @return void
     */
	public function execute()
	{
		throw new Exception('This command shold only be executed using "connect".');
	}

	/**
	 * Connect to the TV
	 * 
	 * The naming pretty wrong here, we actually just make the initial request to check 
	 * if a TV is present.
	 */
	public function connect()
	{
		$this->cli->out('Connecting to ' . $this->container->getParameter('endpoint') . '...');

	    // try to fetch the available service protocols and list them.
	    try {
	    	$protocols = $this->repo('guide')->getServiceProtocols();
	    }
	    catch(Exception $e) 
	    {
	    	return $this->cli->error('Could not connect to the TV. ' . $e->getMessage());
	    }

	    $this->cli->info('Success!');
	    $this->cli->br();

	    // List the available service protocols
	    $this->displayAvailableServiceProtocols($protocols);

	    // continue with the device infos
	    $this->showDeviceInfos();

	    // enter cms loop
	   	$this->startCMDLoop();
	}

	/**
	 * Show device information
	 */
	protected function displayAvailableServiceProtocols(array $protocols)
	{
		$this->cli->blue(count($protocols) . ' service protocols are available: ');

	    $protocolNames = [];
	    foreach($protocols as $p) {
	    	$protocolNames[] = $p[0];
	    }

	    $this->cli->columns($protocolNames);
	}

	/**
	 * Show device information
	 */
	protected function showDeviceInfos()
	{
		$this->cli->br();

		$this->cli->blue('TV Interface:');
		$infos = $this->repo('system')->getInterfaceInformation();

		// show system infos
		$this->cli->columns($infos[0]);
	}

	/**
	 * Execute a diffrent command
	 * 
	 * @param string 			$name
	 */
	protected function executeCommand(string $name)
	{
		$this->cli->br();

		if (!$this->container->has('cmd.' . $name))
		{
			$this->cli->error('There is no command with the name ' . $name . ' available.');
		}

		// reset the stty settings
		system('stty sane');

		// load and run the command
		$cmd = $this->container->get('cmd.' . $name);
		$cmd->execute();

		// go back to the cmd loop settings
		$this->enterRemoteControlMode();
	}

	/**
	 * This will configure stty 
	 */
	protected function enterRemoteControlMode()
	{
		$this->cli->br();
		$this->cli->inline('<blue>[Remote] </blue>');
		system("stty -echo -icanon");
	}

	/**
	 * Enter the command loop 
     */
	protected function startCMDLoop()
	{
		// always refresh the IRCC commands before entering the loop
		$ircc = $this->repo('IRCC');
		$ircc->refreshAvailableCommands();

		$this->cli->br();
		$this->cli->out('<green>Ready!</green> You can now press the arrow keys to navigate your tv. Press "h" to view the keymapping.');

		$this->enterRemoteControlMode();

		while ($c = fread(STDIN, 1)) 
		{
			// mapping 
			// ← = 68
			// → = 67
			// ↑ = 65
			// ↓ = 66
			$key = ord($c);

			switch ($key) 
			{
				// ingore the key prefix
				case 27: case 91: break;

				/**
				 * Navigation
				 */
				case 68:
					$ircc->send('Left');
				break;

				case 67:
					$ircc->send('Right');
				break;

				case 65:
					$ircc->send('Up');
				break;

				case 66:
					$ircc->send('Down');
				break;

				case 10:
					$ircc->send('Confirm');
				break;

				case 127:
					$ircc->send('Return');
				break;

				/**
				 * Run command (99 = c)
				 */
				case 99:
					$this->executeCommand('execute');
				break;

				/**
				 * Forward Raw (102 = f)
				 */
				case 102:
					$this->executeCommand('raw_forward');
				break;

				/**
				 * Print Help (104 = h)
				 */
				case 104:
					$this->executeCommand('remote_help');
				break;
				
				/**
				 * Unknown
				 */
				default:
					$this->cli->error("Unknown Command... ($key)");
				break;
			}
		}
	}
}