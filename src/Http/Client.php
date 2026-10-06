<?php declare(strict_types = 1);

namespace PHPTv\Http;

use PHPTv\Exceptions\LogicException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use function base64_encode;
use function is_array;
use function json_decode;
use function json_encode;
use function preg_match;
use function print_r;

final class Client
{

	private ?string $authCookie = null;

	public function __construct(
		private string $baseUrl,
		private string $psk,
		private ClientInterface $httpClient,
		private RequestFactoryInterface $requestFactory,
		private StreamFactoryInterface $streamFactory,
	)
	{
	}

	public function setAuthCookie(string $cookie): void
	{
		$this->authCookie = $cookie;
	}

	/**
	 * Initiates or confirms device registration for IRCC cookie auth.
	 *
	 * Pass null for $pin on the first call — the TV will display a PIN on screen
	 * and return null here. Pass the displayed PIN to confirm and receive the cookie.
	 *
	 * @throws LogicException
	 */
	public function actRegister(
		string $clientId,
		string $nickname,
		?string $pin = null,
	): ?string
	{
		$body = (string) json_encode([
			'method' => 'actRegister',
			'params' => [
				['clientid' => $clientId, 'nickname' => $nickname],
				[['value' => 'yes', 'function' => 'WOL']],
			],
			'id' => 1,
			'version' => '1.0',
		]);

		$request = $this->requestFactory->createRequest('POST', $this->baseUrl . '/sony/accessControl')
			->withHeader('Content-Type', 'application/json')
			->withBody($this->streamFactory->createStream($body));

		if ($pin !== null) {
			$request = $request->withHeader('Authorization', 'Basic ' . base64_encode(':' . $pin));
		}

		try {
			$response = $this->httpClient->sendRequest($request);
		} catch (ClientExceptionInterface $e) {
			throw new LogicException('actRegister failed: ' . $e->getMessage(), 0, $e);
		}

		if ($response->getStatusCode() === 401) {
			return null;
		}

		if ($response->getStatusCode() !== 200) {
			throw new LogicException('actRegister returned unexpected status: ' . $response->getStatusCode());
		}

		preg_match('/auth=([^;]+)/', $response->getHeaderLine('Set-Cookie'), $matches);

		return $matches[1] ?? '';
	}

	/**
	 * @throws LogicException
	 */
	public function sendIRCC(string $irccCode): string
	{
		$body = '<?xml version="1.0"?>'
			. '<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">'
			. '<s:Body><u:X_SendIRCC xmlns:u="urn:schemas-sony-com:service:IRCC:1">'
			. '<IRCCCode>' . $irccCode . '</IRCCCode>'
			. '</u:X_SendIRCC></s:Body></s:Envelope>';

		$request = $this->requestFactory->createRequest('POST', $this->baseUrl . '/sony/IRCC')
			->withHeader('SOAPACTION', '"urn:schemas-sony-com:service:IRCC:1#X_SendIRCC"')
			->withHeader('Content-Type', 'text/xml; charset=UTF-8')
			->withBody($this->streamFactory->createStream($body));

		if ($this->authCookie !== null) {
			$request = $request->withHeader('Cookie', 'auth=' . $this->authCookie);
		} else {
			$request = $request->withHeader('X-Auth-PSK', $this->psk);
		}

		return $this->send($request);
	}

	/**
	 * @param array<string, string> $params
	 * @return array<mixed>
	 *
	 * @throws LogicException
	 */
	public function requestMethod(
		string $endpoint,
		string $method,
		array $params = [],
		string $version = '1.0',
	): array
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
			throw new LogicException('Could not decode response.');
		}

		if (!isset($response['result']) && !isset($response['results'])) {
			throw new LogicException('Unexpected response from TV Api. ' . print_r($response, true));
		}

		return $response['result'] ?? $response['results'];
	}

	/**
	 * @throws LogicException
	 */
	private function send(RequestInterface $request): string
	{
		try {
			$response = $this->httpClient->sendRequest($request);
		} catch (ClientExceptionInterface $e) {
			throw new LogicException('Request failed: ' . $e->getMessage(), 0, $e);
		}

		$status = $response->getStatusCode();
		if ($status < 200 || $status >= 300) {
			throw new LogicException('Received bad status code: ' . $status . "\n\nURL:\n" . $request->getUri());
		}

		return $response->getBody()->getContents();
	}

}
