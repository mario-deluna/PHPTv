<?php declare(strict_types = 1);

namespace PHPTv\Exceptions;

final class UnexpectedTvResponseException extends RuntimeException
{

	public static function couldNotDecode(): self
	{
		return new self('Could not decode TV API response.');
	}

	public static function unexpectedStructure(string $body): self
	{
		return new self('Unexpected TV API response structure: ' . $body);
	}

}
