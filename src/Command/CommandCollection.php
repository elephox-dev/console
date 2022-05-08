<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use Elephox\Autoloading\Composer\NamespaceLoader;
use Elephox\Console\Command\Contract\CommandHandler;
use Elephox\DI\Contract\Resolver;
use GetOpt\ArgumentException;
use GetOpt\ArgumentException\Missing;
use GetOpt\Command;
use GetOpt\GetOpt;
use GetOpt\HelpInterface;

class CommandCollection
{
	private readonly GetOpt $getOpt;

	public function __construct(private readonly Resolver $resolver)
	{
		$this->getOpt = new GetOpt();
		$this->getOpt->setHelp(new LoggerHelpRenderer());
	}

	public function getGetOpt(): GetOpt
	{
		return $this->getOpt;
	}

	public function add(CommandTemplate $template): void
	{
		$this->getOpt->addCommand($template);
	}

	public function loadFromNamespace(string $namespace): static
	{
		NamespaceLoader::iterateNamespace($namespace, function (string $className): void {
			$this->loadFromClass($className);
		});

		return $this;
	}

	protected function preProcessCommandTemplate(CommandTemplateBuilder $builder): void
	{
		// Do nothing by default
	}

	protected function postProcessCommandTemplate(CommandTemplateBuilder $builder): void
	{
		// Do nothing by default
	}

	/**
	 * @param class-string $className
	 */
	public function loadFromClass(string $className): void
	{
		$interfaces = class_implements($className);
		if (!$interfaces || !in_array(CommandHandler::class, $interfaces, true)) {
			return;
		}

		/** @var CommandHandler $instance */
		$instance = $this->resolver->instantiate($className);

		$templateBuilder = new CommandTemplateBuilder();
		$this->preProcessCommandTemplate($templateBuilder);
		$instance->configure($templateBuilder);
		$this->postProcessCommandTemplate($templateBuilder);
		$template = $templateBuilder->build($instance);

		$this->add($template);
	}

	public function process(InvokedCommandLine $commandLine): null|int|HelpInterface
	{
		try {
			$this->getOpt->process($commandLine->arguments);
		} catch (Missing $missing) {
			throw new InvalidCommandLineException($missing->getMessage(), previous: $missing);
		} catch (ArgumentException) {
			return $this->getHelp();
		}

		$command = $this->getOpt->getCommand();

		if ($command instanceof Command) {
			if ($this->getOpt->getOption('help')) {
				return $this->getHelp();
			}

			/** @var mixed $handler */
			$handler = $command->getHandler();
			if (is_callable($handler)) {
				/** @var int|null $result */
				$result = $this->resolver->callback($handler(...), ['getOpt' => $this->getOpt]);

				if (is_int($result)) {
					return $result;
				}
			}
		} else {
			return $this->getHelp();
		}

		return null;
	}

	public function getHelp(): HelpInterface
	{
		return $this->getOpt->getHelp();
	}

	public function findByName(string $name): ?CommandTemplate
	{
		$command = $this->getOpt->getCommand($name);
		if (!$command) {
			return null;
		}

		if (!$command instanceof CommandTemplate) {
			return null;
		}

		return $command;
	}

//	public function findCompiled(RawCommandInvocation $invocation): CompiledCommandHandler
//	{
//		return $this->templateMap
//			->whereKey(static fn (CommandTemplate $template): bool => $template->name === $invocation->name)
//			->select(static fn (CommandHandler $handler, CommandTemplate $template): CompiledCommandHandler => new CompiledCommandHandler($invocation, $template, $handler))
//			->firstOrDefault(null)
//			?? throw new CommandNotFoundException($invocation->name);
//	}
//
//	public function getTemplateByName(string $name): CommandTemplate
//	{
//		return $this->templateMap
//			->flip()
//			->where(static fn (CommandTemplate $template): bool => $template->name === $name)
//			->firstOrDefault(null)
//		?? throw new CommandNotFoundException($name);
//	}
//
//	public function getIterator(): Iterator
//	{
//		return $this->templateMap->getIterator();
//	}
}
