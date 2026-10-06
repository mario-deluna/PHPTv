<?php declare(strict_types = 1);

namespace PHPTv\Contracts;

use PHPTv\Exceptions\LogicException;

interface RemoteInterface
{

	/**
	 * Returns false if commands could not be loaded (e.g. connection error).
	 */
	public function refreshAvailableCommands(): bool;

	/**
	 * @return array<string, string>
	 */
	public function getAvailableCommands(): array;

	/**
	 * @throws LogicException
	 */
	public function send(string $codeName): void;

}
