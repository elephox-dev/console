<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use Closure;
use Elephox\Support\TransparentProperties;
use GetOpt\GetOpt;
use InvalidArgumentException;
use Stringable;

/**
 * @method null|string getDescription()
 * @method bool hasValue()
 * @method bool doesAllowMultiple()
 * @method mixed getDefaultValue()
 * @method bool hasDefaultValue()
 * @method bool isRequired()
 * @method null|string getValueName()
 * @method null|string getName()
 * @method null|string getShort()
 */
class OptionTemplateBuilder
{
	use TransparentProperties;

	public function __construct(
		private readonly ?string $name,
		private readonly ?string $short,
		private ?string $description = null,
		private bool $hasValue = false,
		private bool $allowsMultiple = false,
		private mixed $defaultValue = null,
		private bool $hasDefaultValue = false,
		private bool $isRequired = false,
		private ?string $valueName = null,
		private ?Closure $validation = null,
		private ?Closure $validationMessage = null,
	) {
		if ($name === null && $short === null) {
			throw new InvalidArgumentException('Argument name and short name cannot be null. Either one or both must be set.');
		}
	}

	public function description(?string $description): self
	{
		$this->description = $description;

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

	public function required(bool $required = true): self
	{
		$this->isRequired = $required;
		$this->hasValue = true;

		return $this;
	}

	public function allowMultiple(bool $allow = true): self
	{
		$this->allowsMultiple = $allow;
		$this->hasValue = true;

		return $this;
	}

	public function valueName(string $name): self
	{
		$this->valueName = $name;

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

	public function build(): OptionTemplate
	{
		$mode = GetOpt::NO_ARGUMENT;

		if ($this->isRequired) {
			$mode = GetOpt::REQUIRED_ARGUMENT;
		} elseif ($this->hasDefaultValue) {
			$mode = GetOpt::OPTIONAL_ARGUMENT;
		}

		if ($this->allowsMultiple) {
			$mode = GetOpt::MULTIPLE_ARGUMENT;
		}

		$option = OptionTemplate::create($this->short, $this->name, $mode);

		if ($this->description !== null) {
			$option->setDescription($this->description);
		}

		if ($this->hasDefaultValue) {
			$option->setDefaultValue($this->defaultValue);
		}

		if ($this->valueName !== null) {
			$option->setArgumentName($this->valueName);
		}

		if ($this->validation !== null) {
			$option->setValidation($this->validation, $this->validationMessage);
		}

		return $option;
	}
}
