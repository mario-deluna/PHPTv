<?php

namespace PHPTv;

class Client
{   
    /**
     * The clients endpoint
     * 
     * @param string
     */
    protected $endpoint;

    /**
     * The clients port
     * 
     * @param int
     */
    protected $port = 80;

    /**
     * PSK Code 
     * 
     * @param string
     */
    protected $psk = '0000';

    /**
     * The clients port
     * 
     * @param int
     */
    const SCHEME_HTTP = 'http';
    const SCHEME_HTTPS = 'https';
    protected $scheme = self::SCHEME_HTTP;

    /**
     * Available methods currently there is just GET & POST...
     */
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';

    /**
     * Construct a new client
     * 
     * @param string        $endpoint The url to the content endpoint
     * @return void
     */
    public function __construct($endpoint, $psk = '0000')
    {
        $this->setEndpoint($endpoint);

        // set the PSK
        $this->psk = $psk;
    }

    /**
     * Inline getters..
     */
    public function getEndpoint() { return $this->endpoint; }
    public function getPort() { return $this->port; }
    public function getScheme() { return $this->scheme; }

    /**
     * Parses and enpoint string
     * 
     * @param endpoint
     * @return void
     */
    public function setEndpoint($endpoint)
    {
        $this->port = 80; // reset port
        $this->scheme = self::SCHEME_HTTP; // reset port

        // remove trailling slash
        if (substr($endpoint, -1) === '/') {
            $endpoint = substr($endpoint, 0, -1);
        }

        // try to parse the schema
        if (strpos($endpoint, '://') !== false) {
            list($this->scheme, $endpoint) = explode('://', $endpoint);
        }

        // parse possible port number
        if (strpos($endpoint, ':') !== false) {
            list($endpoint, $this->port) = explode(':', $endpoint);
        } 
        // if no port isset and its https set the port 443
        elseif ($this->scheme === self::SCHEME_HTTPS) {
            $this->port = 443;
        }

        $this->endpoint = $endpoint;
    }

    /**
     * Set the current api version
     * 
     * @param string            $version
     * @return void
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * Build the url
     * 
     * @param string            $uri
     * @param array             $parameters
     * @return string
     */
    public function buildUrl($uri, array $parameters = [])
    {
    	// port in url only if not 80
    	if ($this->port !== 80) {
    		$port = ':' . $this->port . '/';
    	} else {
    		$port = "";
    	}

        // construct the url
        $url = $this->scheme . '://' . 
            $this->endpoint .
            $port .
            $uri;

        // add the parametres
        return $url . '?' . http_build_query($parameters);
    }

    /**
     * Send IRCC code
     * 
     * @param string 			$irccCode
     * @return void	
     */
    public function sendIRCC($irccCode)
    {
    	$body = "<?xml version=\"1.0\"?><s:Envelope xmlns:s=\"http://schemas.xmlsoap.org/soap/envelope/\" s:encodingStyle=\"http://schemas.xmlsoap.org/soap/encoding/\"><s:Body><u:X_SendIRCC xmlns:u=\"urn:schemas-sony-com:service:IRCC:1\"><IRCCCode>$irccCode</IRCCCode></u:X_SendIRCC></s:Body></s:Envelope>";

    	return $this->request(static::METHOD_POST, '/sony/IRCC', [], $body, [
    		'SOAPACTION: "urn:schemas-sony-com:service:IRCC:1#X_SendIRCC"',
    		'X-Auth-PSK: 0000',
    		'content-type: text/xml; charset=UTF-8'
    	]);
    }

    /**
     * Runs the given method on the TV API
     * 
     * @param string        $method
     * @param array         $params
     * @param string        $endpoint
     *
     * @return string
     */
    public function requestMethod(string $method, array $params = [], $endpoint = 'system')
    {
        return $this->requestJSON(Client::METHOD_POST, '/sony/' . $endpoint, [], [
            "id" => 1,
            "method" => $method,
            "params" => $params,
            "version" => "1.0"
        ]);
    }

    /**
     * Run a request and return the response as array
     * 
     * @param string 		$method
     * @param string 		$uri 
     * @param array 		$parameters
     * @param array 		$body
     * @param array 		$headers
     * 
     * @return string
     */
    public function requestJSON($method, $uri, array $parameters = [], array $body = [], $headers = [])
    {
    	$response = $this->request($method, $uri, $parameters, json_encode($body), [
    		"content-type: application/json",
    	]);

        // try to decode the response
        $response = json_decode($response, true);
        if (!is_array($response)) {
            throw new Exception("Could not decode response. " . $status . "\n\nURL:\n" . $url);
        } 

        return $response;
    }

    /**
     * Run a request and return the response body
     * 
     * @param string 		$method
     * @param string 		$uri 
     * @param array 		$parameters
     * @param string 		$body
     * @param array 		$headers
     * 
     * @return string
     */
    public function request($method, $uri, array $parameters = [], string $body = '', array $headers = [])
    {
        $curl = curl_init();

        // construct the url
        $url = $this->buildUrl($uri, $parameters);

        $curlOptions = [

            // construct the basics
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_PORT => $this->port,
            CURLOPT_URL => $url,

            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_FOLLOWLOCATION => true,
            
            // headers
            CURLOPT_HTTPHEADER => array_merge([
                "cache-control: no-cache",
                "user-agent: PHPTv Client"
            ], $headers),
        ];

        if ($method === self::METHOD_POST) 
        {
            // encode the body data as json
            $curlOptions[CURLOPT_POSTFIELDS] = $body;
        }

        curl_setopt_array($curl, $curlOptions);
        
        // run the request
        $response = curl_exec($curl);

        // check for errors
        if ($error = curl_error($curl)) {
            throw new Exception("Request failed #: " . $error);
        }

        // check status code
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (!($status >= 200 && $status <= 300)) {
            throw new Exception("Recieved bad status code: " . $status . "\n\nURL:\n" . $url . "\n\nResponse:\n" . $response);
        }

        // return the response
        return $response;
    }
}