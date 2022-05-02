<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use ArrayAccess;
use GetOpt\GetOpt;

/**
 * @implements ArrayAccess<string, mixed>
 */
class CommandInvocation implements ArrayAccess
{
	public static function fromGetOpt(GetOpt $getOpt): self
	{
		return new self($getOpt);
	}

	private function __construct(private readonly GetOpt $getOpt)
	{
	}

	public function __get(string $name)
	{
		return $this->offsetGet($name);
	}

	public function __isset(string $name)
	{
		return $this->offsetExists($name);
	}

	public function __set(string $name, mixed $value)
	{
		$this->offsetSet($name, $value);
	}

	public function offsetExists(mixed $offset): bool
	{
		return $this->getOpt->offsetExists($offset);
	}

	/**
	 * @return mixed
	 */
	public function offsetGet(mixed $offset): mixed
	{
		return $this->getOpt->offsetGet($offset);
	}

	public function offsetSet(mixed $offset, mixed $value): void
	{
		$this->getOpt->offsetSet($offset, $value);
	}

	public function offsetUnset(mixed $offset): void
	{
		$this->getOpt->offsetUnset($offset);
	}
}
