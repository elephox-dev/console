<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use Closure;
use Elephox\Support\TransparentProperties;
use GetOpt\Operand;
use Stringable;

/**
 * @method null|string getDescription()
 * @method bool isRequired()
 * @method bool isMultiple()
 */
class ArgumentTemplateBuilder
{
	use TransparentProperties;

	public function __construct(
		private readonly string $name,
		private ?string $description = null,
		private bool $required = false,
		private bool $multiple = false,
		private mixed $defaultValue = null,
		private bool $hasDefaultValue = false,
		private ?Closure $validation = null,
		private ?Closure $validationMessage = null,
	) {
	}

	public function description(?string $description): self
	{
		$this->description = $description;

		return $this;
	}

	public function required(bool $required = true): self
	{
		$this->required = $required;

		return $this;
	}

	public function multiple(bool $multiple = true): self
	{
		$this->multiple = $multiple;

		return $this;
	}

	public function defaultValue(mixed $value): self
	{
		$this->defaultValue = $value;
		$this->hasDefaultValue = true;

		return $this;
	}

	public function removeDefaultValue(): self
	{
		$this->defaultValue = null;
		$this->hasDefaultValue = false;

		return $this;
	}

	public function validator(null|callable $validator, null|string|Stringable|callable $message = null): self
	{
		if ($validator === null) {
			$this->validation = null;
			$this->validationMessage = null;

			return $this;
		}

		$this->validation = $validator(...);

		if ($message !== null) {
			if (is_callable($message)) {
				$this->validationMessage = $message(...);
			} else {
				$this->validationMessage = static fn (): string => (string) $message;
			}
		}

		return $this;
	}

	public function build(): ArgumentTemplate
	{
		$mode = Operand::OPTIONAL;

		if ($this->required) {
			$mode |= Operand::REQUIRED;
		}

		if ($this->multiple) {
			$mode |= Operand::MULTIPLE;
		}

		$argument = new ArgumentTemplate($this->name, $mode);

		if ($this->description !== null) {
			$argument->setDescription($this->description);
		}

		if ($this->hasDefaultValue) {
			$argument->setDefaultValue($this->defaultValue);
		}

		if ($this->validation !== null) {
			$argument->setValidation($this->validation, $this->validationMessage);
		}

		return $argument;
	}
}
