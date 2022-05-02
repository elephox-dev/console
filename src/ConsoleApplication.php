<?php
declare(strict_types=1);

namespace Elephox\Console;

use Elephox\Configuration\Contract\Configuration;
use Elephox\Console\Command\CommandCollection;
use Elephox\Console\Command\CommandNotFoundException;
use Elephox\Console\Command\LoggerHelpRenderer;
use Elephox\Console\Command\NoCommandInCommandLineException;
use Elephox\Console\Command\InvalidCommandLineException;
use Elephox\Console\Contract\ConsoleEnvironment;
use Elephox\DI\Contract\ServiceCollection as ServiceCollectionContract;
use Elephox\Support\Contract\ExceptionHandler;
use GetOpt\HelpInterface;
use Psr\Log\LoggerInterface;

class ConsoleApplication
{
	protected ?LoggerInterface $logger = null;
	protected ?ExceptionHandler $exceptionHandler = null;

	public function __construct(
		public readonly ServiceCollectionContract $services,
		public readonly Configuration $configuration,
		public readonly ConsoleEnvironment $environment,
		public readonly CommandCollection $commands,
	) {
		$this->services->addSingleton(__CLASS__, implementation: $this);
	}

	public function logger(): LoggerInterface
	{
		if ($this->logger === null) {
			$this->logger = $this->services->requireService(LoggerInterface::class);
		}

		return $this->logger;
	}

	public function exceptionHandler(): ExceptionHandler
	{
		if ($this->exceptionHandler === null) {
			$this->exceptionHandler = $this->services->requireService(ExceptionHandler::class);
		}

		return $this->exceptionHandler;
	}

	public function run(): never
	{
		global $argv;

		try {
			$result = $this->commands->process($argv);
			if ($result instanceof LoggerHelpRenderer) {
				$result->renderToLogger($this->logger(), $this->commands->getGetOpt(), []);

				$code = 1;
			} else {
				$code = $result ?? 0;
			}
		} catch (InvalidCommandLineException $e) {
			$this->logger()->error($e->getMessage());

			$this->logger()->error("Use '" . implode(' ', [$argv[0], 'help', $argv[1]]) . "' to get help for this command.");
			$code = 1;
		}

		exit($code);
	}
}
