<?php declare(strict_types = 1);

namespace PHPTv\Crate;

final readonly class IrccRemoteTypeCrate
{

	public function __construct(
		public bool $bundled,
		public string $type,
	)
	{
	}

	/**
	 * @param array{bundled: bool, type: string} $data
	 */
	public static function fromArray(array $data): self
	{
		return new self(
			bundled: $data['bundled'],
			type: $data['type'],
		);
	}

}
