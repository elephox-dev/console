<?php
declare(strict_types=1);

namespace Elephox\Console;

use Elephox\Configuration\Contract\Configuration;
use Elephox\Console\Command\CommandCollection;
use Elephox\Console\Command\InvokedCommandLine;
use Elephox\Console\Command\LoggerHelpRenderer;
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
		try {
			$commandLine = InvokedCommandLine::fromGlobals();
			//$command = $this->commands->findByName($commandLine->arguments[0] ?? 'help');

			$result = $this->commands->process($commandLine);
			if ($result instanceof LoggerHelpRenderer) {
				$result->renderToLogger($this->logger(), $this->commands->getGetOpt());

				$code = 1;
			} elseif ($result instanceof HelpInterface) {
				$result->render($this->commands->getGetOpt());

				$code = 1;
			} else {
				$code = $result ?? 0;
			}
		} catch (InvalidCommandLineException $e) {
			$this->logger()->error($e->getMessage());
			$this->commands->getGetOpt()->getHelpText(['logger' => $this->logger()]);
			$code = 1;
		}

		exit($code);
	}
}
