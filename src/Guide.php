<?php 

namespace PHPTv;

class Guide extends Repository
{
	/**
	 * The current used endpoint string 
	 * 
	 * @var string
	 */
	protected $tvApiEndpoint = 'guide';

	/**
	 * Get the available service protocols
	 */
	public function getServiceProtocols() : array
	{
		return $this->request('getServiceProtocols');
	}
}