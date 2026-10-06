<?php declare(strict_types = 1);

namespace PHPTv\Exceptions;

use Throwable;

final class HttpBadResponseException extends RuntimeException
{

	public static function requestFailed(
		string $context,
		Throwable $previous,
	): self
	{
		return new self($context . ' failed: ' . $previous->getMessage(), $previous);
	}

	public static function badStatusReceived(
		int $status,
		string $url,
	): self
	{
		return new self('Received bad status code: ' . $status . "\n\nURL:\n" . $url);
	}

}
