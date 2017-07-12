<?php 

namespace PHPTv;

use ClanCats\Container\Container;
use League\Climate\Climate;

class PHPTv 
{
	/**
	 * The container
	 */
	protected $container;

	/**
	 * The command line interface
	 */
	protected $cli;

	/**
	 * Construct the PHP Tv CLi app
	 */
	public function __construct(Container $container, Climate $cli)
	{
		$this->container = $container;
		$this->cli = $cli;
	}

	/**
	 * Shortcut to laod a repo
	 */
	private function repo($name)
	{
		return $this->container->get('repo.'.$name);
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

	    $this->cli->blue(count($protocols) . ' service protocols are available: ');

	    $protocolNames = [];
	    foreach($protocols as $p) {
	    	$protocolNames[] = $p[0];
	    }

	    $this->cli->columns($protocolNames);

	    // continue with the device infos
	    $this->showDeviceInfos();

	    // enter cms loop
	   	$this->startCMDLoop();
	}

	/**
	 * Show device information
	 */
	public function showDeviceInfos()
	{
		$this->cli->br();

		$this->cli->blue('TV Interface:');
		$infos = $this->repo('system')->getInterfaceInformation();

		// show system infos
		$this->cli->columns($infos[0]);
	}

	/**
	 * Enter the command loop 
     */
	public function startCMDLoop()
	{
		$ircc = $this->repo('IRCC');
		$ircc->refreshAvailableCommands();

		$this->cli->br();
		$this->cli->blue('Available commands:');
		$this->cli->columns($ircc->getAvailableCommands());

		$input = $this->cli->input('> ');

		while (1) 
		{
			$ircc->send(ucfirst($input->prompt()));
		}
	}
}