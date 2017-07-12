<?php 

namespace PHPTv;

class Repository 
{
	use ClientAwareTrait;

	/**
	 * The current used endpoint string 
	 * 
	 * @var string
	 */
	protected $tvApiEndpoint = null;

	/**
	 * Get the available service protocols
	 */
	public function request(string $method, array $params = [], $version = '1.0') : array
	{
		return $this->client->requestMethod($this->tvApiEndpoint, $method, $params, $version);
	}
}