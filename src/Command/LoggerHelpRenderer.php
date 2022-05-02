<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use GetOpt\CommandInterface;
use GetOpt\GetOpt;
use GetOpt\HelpInterface;
use GetOpt\Operand;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;

class LoggerHelpRenderer implements HelpInterface
{
	public function renderToLogger(LoggerInterface $logger, GetOpt $getopt, array $data = []): void
	{
		$this->renderUsage($logger, $getopt);

		if ($getopt->hasOperands()) {
			$this->renderOperands($logger, $getopt);
		}

		if ($getopt->hasOptions()) {
			$this->renderOptions($logger, $getopt);
		}

		if ($getopt->hasCommands() && $command = $getopt->getCommand()) {
			$this->renderCommand($logger, $command);
		} else {
			$this->renderCommands($logger, $getopt);
		}
	}

	public function render(GetOpt $getopt, array $data = []): string
	{
		if (!array_key_exists('logger', $data)) {
			throw new InvalidArgumentException('Missing logger in data');
		}

		if (!$data['logger'] instanceof LoggerInterface) {
			throw new InvalidArgumentException('Logger must implement LoggerInterface');
		}

		$logger = $data['logger'];

		$this->renderToLogger($logger, $getopt, $data);

		return '';
	}

	private function renderUsage(LoggerInterface $logger, GetOpt $getopt): void
	{
		$logger->info(sprintf(
			'Usage: %s%s%s',
			$this->getUsageCommand($getopt),
			$this->getUsageArguments($getopt),
			$this->getUsageOptions($getopt),
		));
	}

	private function getUsageCommand(GetOpt $getopt): string
	{
		if ($command = $getopt->getCommand()) {
			return $command->getName() . ' ';
		}

		if ($getopt->hasCommands()) {
			return '<command> ';
		}

		return '';
	}

	private function getUsageArguments(GetOpt $getopt): string
	{
		return '<args> ';
	}

	private function getUsageOptions(GetOpt $getopt): string
	{
		return '<options>';
	}

	private function renderOperands(LoggerInterface $logger, GetOpt $getopt): void
	{
		$logger->info('Operands:');

		foreach ($getopt->getOperands() as $operand) {
			$logger->info(sprintf(
				'  %s%s',
				$operand->getName(),
				$this->getOperandDescription($operand)
			));
		}
	}

	private function getOperandDescription(Operand $operand): string
	{
		if ($operand->isRequired()) {
			return ' (required)';
		}

		if ($operand->isMultiple()) {
			return ' (multiple)';
		}

		return '';
	}

	private function renderOptions(LoggerInterface $logger, GetOpt $getopt): void
	{
		$logger->info('Options:');

		foreach ($getopt->getOptions() as $name => $option) {
			$logger->info(sprintf(
				'  %s%s%s',
				$name,
				$this->getOptionDescription($option),
				$this->getOptionDefault($option),
			));
		}
	}

	private function getOptionDescription(mixed $option): string
	{
		if ($option->isRequired()) {
			return ' (required)';
		}

		if ($option->isMultiple()) {
			return ' (multiple)';
		}

		return '';
	}

	private function getOptionDefault(mixed $option): string
	{
		if ($option->isRequired()) {
			return '';
		}

		if ($option->isMultiple()) {
			return '';
		}

		return ' (default: ' . $option->getDefault() . ')';
	}

	private function renderCommand(LoggerInterface $logger, CommandInterface $command): void
	{
		$logger->info(sprintf(
			'Command: %s',
			$command->getName()
		));

		$this->renderDescription($logger, $command);
		$this->renderOperands($logger, $command);
		$this->renderOptions($logger, $command);
	}

	private function renderDescription(LoggerInterface $logger, CommandInterface $command)
	{
		$logger->info(sprintf(
			'Description: %s',
			$command->getDescription()
		));
	}

	private function renderCommands(LoggerInterface $logger, GetOpt $getopt)
	{
		$logger->info('Commands:');

		foreach ($getopt->getCommands() as $command) {
			$logger->info(sprintf(
				'  %s%s',
				$command->getName(),
				$this->getCommandDescription($command)
			));
		}
	}

	private function getCommandDescription(CommandInterface $command)
	{
		if ($command->getDescription()) {
			return ' - ' . $command->getDescription();
		}

		return '';
	}
}
