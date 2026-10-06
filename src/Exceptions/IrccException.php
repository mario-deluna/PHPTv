<?php declare(strict_types = 1);

namespace PHPTv\Exceptions;

use Throwable;

final class IrccException extends RuntimeException
{

	public static function sessionExpiredPinRequired(): self
	{
		return new self('IRCC session expired and re-authentication requires a PIN. Restart the app.');
	}

	public static function reAuthenticationFailed(Throwable $previous): self
	{
		return new self('IRCC re-authentication succeeded but command still failed.', $previous);
	}

}
