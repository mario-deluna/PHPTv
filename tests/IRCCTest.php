<?php declare(strict_types = 1);

namespace PHPTv\Tests;

use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use PHPTv\Exceptions\HttpBadResponseException;
use PHPTv\Exceptions\IrccException;
use PHPTv\Exceptions\LogicException;
use PHPTv\Http\Client;
use PHPTv\IRCC;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use StrictPhp\HttpClients\Clients\Mock\MockClient;
use function base64_encode;
use function json_decode;
use function json_encode;
use function md5;

final class IRCCTest extends TestCase
{

	private const PSK = '0000';
	private const BASE_URL = 'http://tv.local';

	/**
	 * @var list<RequestInterface>
	 */
	private array $requests = [];

	protected function setUp(): void
	{
		$this->requests = [];
	}

	public function testRefreshAvailableCommandsLoadsCommands(): void
	{
		$irccClient = $this->createIrcc([$this->commandsResponse()]);

		$irccClient->refreshAvailableCommands();

		self::assertSame(['VolumeUp' => 'AAAA', 'Mute' => 'BBBB'], $irccClient->getAvailableCommands());
		self::assertCount(1, $this->requests);
		self::assertSame(self::BASE_URL . '/sony/system', (string) $this->requests[0]->getUri());
		self::assertSame(self::PSK, $this->requests[0]->getHeaderLine('X-Auth-PSK'));
	}

	public function testRefreshAvailableCommandsKeepsStateOnBadResponse(): void
	{
		$irccClient = $this->createIrcc([$this->commandsResponse(), new Response(500), new Response(200, [], '{"foo":1}')]);
		$irccClient->refreshAvailableCommands();

		$irccClient->refreshAvailableCommands();
		$irccClient->refreshAvailableCommands();

		self::assertSame(['VolumeUp' => 'AAAA', 'Mute' => 'BBBB'], $irccClient->getAvailableCommands());
	}

	public function testStartAuthenticationReturnsTrueAndStoresCookie(): void
	{
		$irccClient = $this->createIrcc([
			new Response(200, ['Set-Cookie' => 'auth=secret; Path=/']),
			$this->commandsResponse(),
			new Response(200),
		]);

		self::assertTrue($irccClient->startAuthentication());

		$irccClient->refreshAvailableCommands();
		$irccClient->send('VolumeUp');

		$register = $this->requests[0];
		self::assertSame(self::BASE_URL . '/sony/accessControl', (string) $register->getUri());
		self::assertFalse($register->hasHeader('Authorization'));
		self::assertSame(md5('PHPTv:1'), $this->decodeBody($register)['params'][0]['clientid']);
		self::assertSame('PHPTv', $this->decodeBody($register)['params'][0]['nickname']);

		$send = $this->requests[2];
		self::assertSame('auth=secret', $send->getHeaderLine('Cookie'));
		self::assertFalse($send->hasHeader('X-Auth-PSK'));
	}

	public function testStartAuthenticationReturnsFalseWhenPinRequired(): void
	{
		$irccClient = $this->createIrcc([new Response(401)]);

		self::assertFalse($irccClient->startAuthentication());
	}

	public function testStartAuthenticationThrowsOnBadStatus(): void
	{
		$irccClient = $this->createIrcc([new Response(500)]);

		$this->expectException(HttpBadResponseException::class);

		$irccClient->startAuthentication();
	}

	public function testConfirmAuthenticationSendsPinAndStoresCookie(): void
	{
		$irccClient = $this->createIrcc([
			new Response(200, ['Set-Cookie' => 'auth=pinCookie; Path=/']),
			$this->commandsResponse(),
			new Response(200),
		]);

		$irccClient->confirmAuthentication('1234');
		$irccClient->refreshAvailableCommands();
		$irccClient->send('Mute');

		self::assertSame('Basic ' . base64_encode(':1234'), $this->requests[0]->getHeaderLine('Authorization'));
		self::assertSame('auth=pinCookie', $this->requests[2]->getHeaderLine('Cookie'));
	}

	public function testConfirmAuthenticationThrowsOnInvalidPin(): void
	{
		$irccClient = $this->createIrcc([new Response(401)]);

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('invalid PIN');

		$irccClient->confirmAuthentication('9999');
	}

	public function testSendPostsIrccCodeWithPsk(): void
	{
		$irccClient = $this->createIrcc([$this->commandsResponse(), new Response(200)]);
		$irccClient->refreshAvailableCommands();

		$irccClient->send('VolumeUp');

		$request = $this->requests[1];
		self::assertSame(self::BASE_URL . '/sony/IRCC', (string) $request->getUri());
		self::assertSame(self::PSK, $request->getHeaderLine('X-Auth-PSK'));
		self::assertStringContainsString('<IRCCCode>AAAA</IRCCCode>', (string) $request->getBody());
	}

	public function testSendThrowsOnUnknownCommand(): void
	{
		$irccClient = $this->createIrcc([]);

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Invalid command "Nope"');

		$irccClient->send('Nope');
	}

	public function testSendReauthenticatesAndRetriesOnExpiredSession(): void
	{
		$irccClient = $this->createIrcc([
			$this->commandsResponse(),
			new Response(403),
			new Response(200, ['Set-Cookie' => 'auth=fresh; Path=/']),
			new Response(200),
		]);
		$irccClient->refreshAvailableCommands();

		$irccClient->send('VolumeUp');

		self::assertCount(4, $this->requests);
		self::assertSame(self::BASE_URL . '/sony/accessControl', (string) $this->requests[2]->getUri());
		self::assertSame('auth=fresh', $this->requests[3]->getHeaderLine('Cookie'));
		self::assertStringContainsString('<IRCCCode>AAAA</IRCCCode>', (string) $this->requests[3]->getBody());
	}

	public function testSendThrowsWhenPinRequiredAfterExpiredSession(): void
	{
		$irccClient = $this->createIrcc([$this->commandsResponse(), new Response(401), new Response(401)]);
		$irccClient->refreshAvailableCommands();

		$this->expectException(IrccException::class);
		$this->expectExceptionMessage('requires a PIN');

		$irccClient->send('VolumeUp');
	}

	public function testSendThrowsWhenRetryAfterReauthenticationFails(): void
	{
		$irccClient = $this->createIrcc([
			$this->commandsResponse(),
			new Response(401),
			new Response(200, ['Set-Cookie' => 'auth=fresh; Path=/']),
			new Response(401),
		]);
		$irccClient->refreshAvailableCommands();

		$this->expectException(IrccException::class);
		$this->expectExceptionMessage('re-authentication succeeded but command still failed');

		$irccClient->send('VolumeUp');
	}

	public function testSendThrowsOnServerError(): void
	{
		$irccClient = $this->createIrcc([$this->commandsResponse(), new Response(500)]);
		$irccClient->refreshAvailableCommands();

		$this->expectException(HttpBadResponseException::class);

		$irccClient->send('VolumeUp');
	}

	public function testCustomAppNameChangesRegistration(): void
	{
		$irccClient = $this->createIrcc([new Response(401)], 'MyApp');

		$irccClient->startAuthentication();

		$params = $this->decodeBody($this->requests[0])['params'][0];
		self::assertSame(md5('MyApp:1'), $params['clientid']);
		self::assertSame('MyApp', $params['nickname']);
	}

	/**
	 * @param list<ResponseInterface> $responses
	 */
	private function createIrcc(array $responses, string $appName = 'PHPTv'): IRCC
	{
		$callbacks = [];
		foreach ($responses as $response) {
			$callbacks[] = function (RequestInterface $request) use ($response): ResponseInterface {
				$this->requests[] = $request;
				return $response;
			};
		}

		$factory = new HttpFactory();
		$client = new Client(self::BASE_URL, self::PSK, new MockClient($callbacks), $factory, $factory);

		return new IRCC($client, $appName);
	}

	private function commandsResponse(): ResponseInterface
	{
		return new Response(200, [], (string) json_encode([
			'id' => 1,
			'result' => [
				[],
				[
					['name' => 'VolumeUp', 'value' => 'AAAA'],
					['name' => 'Mute', 'value' => 'BBBB'],
				],
			],
		]));
	}

	/**
	 * @return array<mixed>
	 */
	private function decodeBody(RequestInterface $request): array
	{
		return (array) json_decode((string) $request->getBody(), true);
	}

}
