<?php 

namespace PHPTv;

class System extends Repository
{
	/**
	 * The current used endpoint string 
	 * 
	 * @var string
	 */
	protected $tvApiEndpoint = 'system';

	/**
	 * Get information about the tv
	 */
	public function getInterfaceInformation() : array
	{
		return $this->request('getInterfaceInformation');
	}
}