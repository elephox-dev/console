<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use Elephox\Collection\ArrayList;

class InvokedCommandLine
{
	/**
	 * @param null|array<int, string> $argv
	 */
	public static function fromGlobals(?array $argv = null): self
	{
		if ($argv === null) {
			/** @var array<int, string> $argv */
			$argv = $_SERVER['argv'] ?? [];
		}

		if (empty($argv)) {
			return new self('<unknown>', []);
		}

		$binary = array_shift($argv);

		return new self($binary, $argv);
	}

	/**
	 * @param string $binary
	 * @param array<int, string> $arguments
	 */
	private function __construct(
		public readonly string $binary,
		public readonly array $arguments,
	) {
	}

	public function enumerateArguments(): ArrayList
	{
		return new ArrayList($this->arguments);
	}
}
