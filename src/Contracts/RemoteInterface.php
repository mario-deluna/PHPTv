<?php declare(strict_types = 1);

namespace PHPTv\Contracts;

use PHPTv\Exceptions\IrccException;

interface RemoteInterface
{

	public function refreshAvailableCommands(): void;

	/**
	 * @return array<string, string>
	 */
	public function getAvailableCommands(): array;

	/**
	 * @throws IrccException
	 */
	public function send(string $codeName): void;

}
