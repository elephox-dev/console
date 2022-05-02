<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use GetOpt\Command;

class CommandTemplate extends Command
{
	/**
	 * Command constructor.
	 *
	 * @param string $name
	 * @param callable(\GetOpt\GetOpt): (int|null) $handler
	 * @param list<OptionTemplate>|null $options
	 * @param list<ArgumentTemplate>|null $arguments
	 */
	public function __construct($name, $handler, $options = null, ?array $arguments = null)
	{
		parent::__construct($name, $handler, $options);

		if ($arguments !== null) {
			$this->addOperands($arguments);
		}
	}
}
