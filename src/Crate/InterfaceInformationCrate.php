<?php declare(strict_types = 1);

namespace PHPTv\Crate;

final readonly class InterfaceInformationCrate
{

	public function __construct(
		public string $productCategory,
		public string $productName,
		public string $modelName,
		public string $serverName,
		public string $interfaceVersion,
	)
	{
	}

	/**
	 * @param array<string, string> $data
	 */
	public static function fromArray(array $data): self
	{
		return new self(
			productCategory: $data['productCategory'],
			productName: $data['productName'],
			modelName: $data['modelName'],
			serverName: $data['serverName'],
			interfaceVersion: $data['interfaceVersion'],
		);
	}

}
