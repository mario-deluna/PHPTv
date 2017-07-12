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
		return $this->container->get($name);
	}

	/**
	 * Connect to the TV
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

	    $this->cli->out(count($protocols) . ' service protocols are available: ');

	    $protocolNames = [];
	    foreach($protocols as $p) {
	    	$protocolNames[] = $p[0];
	    }

	    $this->cli->columns($protocolNames);
	}
}