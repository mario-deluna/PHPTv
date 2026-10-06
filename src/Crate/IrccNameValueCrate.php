<?php declare(strict_types = 1);

namespace PHPTv\Crate;

final readonly class IrccNameValueCrate
{

	public function __construct(
		public string $name,
		public string $value,
	)
	{
	}

	/**
	 * @param array{name: string, value: string} $data
	 */
	public static function fromArray(array $data): self
	{
		return new self(
			name: $data['name'],
			value: $data['value'],
		);
	}

}
