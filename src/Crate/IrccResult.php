<?php declare(strict_types = 1);

namespace PHPTv\Crate;

final readonly class IrccResult
{

	/**
	 * @param list<IrccNameValueCrate> $results
	 */
	public function __construct(
		public IrccRemoteTypeCrate $remoteTypeCrate,
		public array $results,
	)
	{
	}

}
