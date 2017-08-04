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
     * PSK 
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
     * @return string	
     */
    public function sendIRCC($irccCode) : string
    {
    	$body = "<?xml version=\"1.0\"?><s:Envelope xmlns:s=\"http://schemas.xmlsoap.org/soap/envelope/\" s:encodingStyle=\"http://schemas.xmlsoap.org/soap/encoding/\"><s:Body><u:X_SendIRCC xmlns:u=\"urn:schemas-sony-com:service:IRCC:1\"><IRCCCode>$irccCode</IRCCCode></u:X_SendIRCC></s:Body></s:Envelope>";

    	return $this->request(static::METHOD_POST, '/sony/IRCC', [], $body, [
    		'SOAPACTION: "urn:schemas-sony-com:service:IRCC:1#X_SendIRCC"',
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
     * Every endpoint always support the following methods:
     * 
     *  * {"id": 1, "method":"getVersions","version":"1.0","params":[]}
     *  * {"id": 1, "method": "getMethodTypes", "version":"1.0", "params":["1.0"]}
     *
     * That way we can discover whats around.
     * 
     * Enpoints I found are:
     *  * guide
     *  * system
     *  * videoScreen
     *  * audio
     *  * avContent
     *  * etc.
     * 
     * @return array
     */
    public function requestMethod(string $endpoint, string $method, array $params = [], $version = '1.0') : array
    {
        $response = $this->requestJSON(Client::METHOD_POST, '/sony/' . $endpoint, [], [
            "id" => 1,
            "method" => $method,
            "params" => $params,
            "version" => $version
        ]);

        if ((!isset($response['result'])) && (!isset($response['results']))) {
            throw new Exception("Unexpected response from TV Api. " . print_r($response, true));
        }

        return isset($response['result']) ? $response['result'] : $response['results'];
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
     * @return array
     */
    public function requestJSON($method, $uri, array $parameters = [], array $body = [], $headers = []) : array
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
    public function request($method, $uri, array $parameters = [], string $body = '', array $headers = []) : string
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
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_FOLLOWLOCATION => true,
            
            // headers
            CURLOPT_HTTPHEADER => array_merge([
                "cache-control: no-cache",
                "user-agent: PHPTv Client",
                'X-Auth-PSK: ' . $this->psk,
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