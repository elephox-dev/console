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
	public function renderToLogger(LoggerInterface $logger, GetOpt $getOpt): void
	{
		$this->renderUsage($logger, $getOpt);

		if ($getOpt->hasOperands()) {
			$this->renderOperands($logger, $getOpt);
		}

		if ($getOpt->hasOptions()) {
			$this->renderOptions($logger, $getOpt);
		}

		if ($getOpt->hasCommands() && $command = $getOpt->getCommand()) {
			$this->renderCommand($logger, $getOpt, $command);
		} else {
			$this->renderCommands($logger, $getOpt);
		}
	}

	public function render(GetOpt $getOpt, array $data = []): string
	{
		if (!array_key_exists('logger', $data)) {
			throw new InvalidArgumentException('Missing logger in data');
		}

		if (!$data['logger'] instanceof LoggerInterface) {
			throw new InvalidArgumentException('Logger must implement LoggerInterface');
		}

		$logger = $data['logger'];

		$this->renderToLogger($logger, $getOpt);

		return '';
	}

	private function renderUsage(LoggerInterface $logger, GetOpt $getOpt): void
	{
		$logger->info(sprintf(
			'Usage: %s%s%s',
			$this->getUsageCommand($getOpt),
			$this->getUsageArguments($getOpt),
			$this->getUsageOptions($getOpt),
		));
	}

	private function getUsageCommand(GetOpt $getOpt): string
	{
		if ($command = $getOpt->getCommand()) {
			return $command->getName() . ' ';
		}

		if ($getOpt->hasCommands()) {
			return '<command> ';
		}

		return '';
	}

	private function getUsageArguments(GetOpt $getOpt): string
	{
		return '<args> ';
	}

	private function getUsageOptions(GetOpt $getOpt): string
	{
		return '<options>';
	}

	private function renderOperands(LoggerInterface $logger, GetOpt $getOpt): void
	{
		$logger->info('Operands:');

		foreach ($getOpt->getOperands() as $operand) {
			$logger->info(sprintf(
				'  %s%s',
				$operand->getName(),
				$this->getOperandDescription($operand),
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

	private function renderOptions(LoggerInterface $logger, GetOpt $getOpt): void
	{
		$logger->info('Options:');

		foreach ($getOpt->getOptions() as $name => $option) {
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

	private function renderCommand(LoggerInterface $logger, GetOpt $getOpt, CommandInterface $command): void
	{
		$logger->info(sprintf(
			'Command: %s',
			$command->getName(),
		));

		$this->renderDescription($logger, $command);
		$this->renderOperands($logger, $getOpt);
		$this->renderOptions($logger, $getOpt);
	}

	private function renderDescription(LoggerInterface $logger, CommandInterface $command): void
	{
		$logger->info(sprintf(
			'Description: %s',
			$command->getDescription(),
		));
	}

	private function renderCommands(LoggerInterface $logger, GetOpt $getOpt): void
	{
		$logger->info('Commands:');

		foreach ($getOpt->getCommands() as $command) {
			$logger->info(sprintf(
				'  %s%s',
				$command->getName(),
				$this->getCommandDescription($command),
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
