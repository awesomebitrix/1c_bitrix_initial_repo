<?php

namespace Acme;

use Rocketeer\Abstracts\Strategies\AbstractStrategy;
use Rocketeer\Interfaces\Strategies\MigrateStrategyInterface;

class BitrixMigrateStrategy extends AbstractStrategy implements MigrateStrategyInterface
{
	 /**
	 * @type string
	 */
	protected $description = 'Migrates your database with bitrix CLI';
	
	/**
	 * Whether this particular strategy is runnable or not.
	 *
	 * @return bool
	 */
	public function isExecutable()
	{
		return true;
	}

	/**
	 * Run outstanding migrations.
	 *
	 * @return bool|null
	 */
	public function migrate()
	{
		$this->runForCurrentRelease([
			$this->php()->getCommand("cli.php bxmigrate:up"),
		]);
	}

	/**
	 * Seed the database.
	 *
	 * @return bool|null
	 */
	public function seed()
	{
		return $this->migrate();
	}
}