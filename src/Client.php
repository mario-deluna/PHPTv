<?php declare(strict_types=1);

namespace PHPTv;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class Client
{
    public function __construct(
        private string $baseUrl,
        private string $psk,
        private ClientInterface $httpClient,
        private RequestFactoryInterface $requestFactory,
        private StreamFactoryInterface $streamFactory,
    ) {
    }

    public function sendIRCC(string $irccCode): string
    {
        $body = '<?xml version="1.0"?>'
            . '<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">'
            . '<s:Body><u:X_SendIRCC xmlns:u="urn:schemas-sony-com:service:IRCC:1">'
            . '<IRCCCode>' . $irccCode . '</IRCCCode>'
            . '</u:X_SendIRCC></s:Body></s:Envelope>';

        $request = $this->requestFactory->createRequest('POST', $this->baseUrl . '/sony/IRCC')
            ->withHeader('X-Auth-PSK', $this->psk)
            ->withHeader('SOAPACTION', '"urn:schemas-sony-com:service:IRCC:1#X_SendIRCC"')
            ->withHeader('Content-Type', 'text/xml; charset=UTF-8')
            ->withBody($this->streamFactory->createStream($body));

        return $this->send($request);
    }

    /**
     * @param array<mixed> $params
     * @return array<mixed>
     */
    public function requestMethod(string $endpoint, string $method, array $params = [], string $version = '1.0'): array
    {
        $request = $this->requestFactory->createRequest('POST', $this->baseUrl . '/sony/' . $endpoint)
            ->withHeader('X-Auth-PSK', $this->psk)
            ->withHeader('Content-Type', 'application/json')
            ->withBody($this->streamFactory->createStream((string) json_encode([
                'id' => 1,
                'method' => $method,
                'params' => $params,
                'version' => $version,
            ])));

        $responseBody = $this->send($request);
        $response = json_decode($responseBody, true);

        if (!is_array($response)) {
            throw new Exception('Could not decode response.');
        }

        if (!isset($response['result']) && !isset($response['results'])) {
            throw new Exception('Unexpected response from TV Api. ' . print_r($response, true));
        }

        return $response['result'] ?? $response['results'];
    }

    private function send(\Psr\Http\Message\RequestInterface $request): string
    {
        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new Exception('Request failed: ' . $e->getMessage(), 0, $e);
        }

        $status = $response->getStatusCode();
        if ($status < 200 || $status >= 300) {
            throw new Exception('Received bad status code: ' . $status . "\n\nURL:\n" . $request->getUri());
        }

        return $response->getBody()->getContents();
    }
}
