<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use Elephox\Collection\ArrayList;
use Elephox\Console\Command\Contract\CommandHandler;
use GetOpt\GetOpt;
use InvalidArgumentException;

class CommandTemplateBuilder
{
	/**
	 * @param null|string $name
	 * @param null|string $description
	 * @param null|string $shortDescription
	 * @param null|ArrayList<ArgumentTemplateBuilder> $arguments
	 * @param null|ArrayList<OptionTemplateBuilder> $options
	 */
	public function __construct(
		private ?string $name = null,
		private ?string $description = null,
		private ?string $shortDescription = null,
		private ?ArrayList $arguments = null,
		private ?ArrayList $options = null,
	) {
	}

	public function name(string $name): self
	{
		$this->name = $name;

		return $this;
	}

	public function description(string $description): self
	{
		$this->description = $description;

		return $this;
	}

	public function shortDescription(string $shortDescription): self
	{
		$this->shortDescription = $shortDescription;

		return $this;
	}

	public function option(?string $name = null, ?string $short = null): OptionTemplateBuilder
	{
		/** @var ArrayList<OptionTemplateBuilder> */
		$this->options ??= new ArrayList();

		$optionBuilder = new OptionTemplateBuilder($name, $short);
		$this->options->add($optionBuilder);

		return $optionBuilder;
	}

	public function argument(string $name): ArgumentTemplateBuilder
	{
		/** @var ArrayList<ArgumentTemplateBuilder> */
		$this->arguments ??= new ArrayList();

		$argumentBuilder = new ArgumentTemplateBuilder($name);
		$this->arguments->add($argumentBuilder);

		return $argumentBuilder;
	}

	public function build(CommandHandler $handler): CommandTemplate
	{
		$command = new CommandTemplate(
			$this->name ?? throw new InvalidArgumentException('Command name is required'),
			static fn (GetOpt $getOpt): int|null => $handler->handle(CommandInvocation::fromGetOpt($getOpt)),
			$this->options?->select(static fn (OptionTemplateBuilder $builder): OptionTemplate => $builder->build())->toList(),
			$this->arguments?->select(static fn (ArgumentTemplateBuilder $builder): ArgumentTemplate => $builder->build())->toList(),
		);

		if ($this->description !== null) {
			$command->setDescription($this->description);
		}

		if ($this->shortDescription !== null) {
			$command->setShortDescription($this->shortDescription);
		}

		return $command;
	}
}
