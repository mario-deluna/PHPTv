<?php declare(strict_types = 1);

namespace PHPTv\Crate;

final readonly class ServiceProtocolCrate
{

	/**
	 * @param list<string> $protocols
	 */
	public function __construct(
		public string $name,
		public array $protocols,
	)
	{
	}

	/**
	 * @param array{0: string, 1: list<string>} $data
	 */
	public static function fromArray(array $data): self
	{
		return new self(
			name: $data[0],
			protocols: $data[1],
		);
	}

}
