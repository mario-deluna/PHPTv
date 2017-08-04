<?php 

namespace PHPTv;

class Audio extends Repository
{
	/**
	 * The current used endpoint string 
	 * 
	 * @var string
	 */
	protected $tvApiEndpoint = 'audio';

	/**
	 * Get information about the tv
	 */
	public function getVolumeInformation() : array
	{
		return $this->request('getVolumeInformation')[0] ?? [];
	}
}