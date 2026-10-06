<?php declare(strict_types = 1);

namespace PHPTv\Exceptions;

final class HttpAuthException extends RuntimeException
{

	public static function create(int $status): self
	{
		return new self('IRCC session expired (HTTP ' . $status . ').');
	}

}
