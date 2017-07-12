<?php 

namespace PHPTv;

trait ClientAwareTrait 
{
	/**
	 * A Client instance
	 * 
	 * @var Client
	 */
	protected $client;

	/**
     * Construct a auth instance
     * 
     * @param string        $endpoint The url to the content endpoint
     * @return void
     */
    public function __construct(Client $client)
    {
       	$this->client = $client;
    }
}