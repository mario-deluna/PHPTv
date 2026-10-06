<?php declare(strict_types = 1);

namespace PHPTv\Exceptions;

use RuntimeException as PhpRuntimeException;
use Throwable;

abstract class RuntimeException extends PhpRuntimeException
{

	public function __construct(
		string $message = '',
		?Throwable $previous = null,
	)
	{
		parent::__construct($message, $previous === null ? 0 : $previous->getCode(), $previous);
	}

}
