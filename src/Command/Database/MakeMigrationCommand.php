<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Command\Database;

use Illuminate\Database\Console\Migrations\TableGuesser;
use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Str;
use Joomla\ApiDocumentation\Database\Migrations\MigrationCreator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Database make migration command
 */
final class MakeMigrationCommand extends AbstractMigrationCommand
{
	/**
	 * The default command name
	 *
	 * @var  string|null
	 */
	protected static $defaultName = 'database:make-migration';

	/**
	 * The migration creator.
	 *
	 * @var  MigrationCreator
	 */
	private $creator;

	/**
	 * Instantiate the command.
	 *
	 * @param   MigrationCreator              $creator              The migration creator.
	 * @param   Migrator                      $migrator             The database migrator.
	 * @param   MigrationRepositoryInterface  $migrationRepository  The migrations repository.
	 */
	public function __construct(MigrationCreator $creator, Migrator $migrator, MigrationRepositoryInterface $migrationRepository)
	{
		parent::__construct($migrator, $migrationRepository);

		$this->creator = $creator;
	}

	/**
	 * Internal function to execute the command.
	 *
	 * @param   InputInterface   $input   The input to inject into the command.
	 * @param   OutputInterface  $output  The output to inject into the command.
	 *
	 * @return  integer  The command exit code
	 */
	protected function doExecute(InputInterface $input, OutputInterface $output): int
	{
		$symfonyStyle = new SymfonyStyle($input, $output);

		$symfonyStyle->title('Database Make Migration');

		$name = Str::snake(trim($input->getArgument('name')));

		$table  = $input->getOption('table');
		$create = $input->getOption('create') ?: false;

		// If no table was given as an option but a create option is given then we will use the "create" option as the table name.
		if (!$table && is_string($create))
		{
			$table  = $create;
			$create = true;
		}

		// Next, we will attempt to guess the table name if this the migration has "create" in the name.
		if (!$table)
		{
			[$table, $create] = TableGuesser::guess($name);
		}

		$this->writeMigration($symfonyStyle, $name, $table, $create);

		return 0;
	}

	/**
	 * Configures the current command.
	 *
	 * @return  void
	 */
	protected function configure(): void
	{
		parent::configure();

		$this->setDescription('Create a new database migration');
		$this->addArgument('name', InputArgument::REQUIRED, 'The name of the migration.');
		$this->addOption('create', null, InputOption::VALUE_OPTIONAL, 'The table to be created.');
		$this->addOption('table', null, InputOption::VALUE_OPTIONAL, 'The table to migrate.');
	}

	/**
	 * Write the migration file to disk.
	 *
	 * @param   SymfonyStyle  $symfonyStyle  The output object.
	 * @param   string        $name          The name of the migration.
	 * @param   string        $table         The name of the table being migrated.
	 * @param   boolean       $create        Flag indicating the table is being created.
	 *
	 * @return  void
	 */
	private function writeMigration(SymfonyStyle $symfonyStyle, ?string $name, ?string $table, ?bool $create)
	{
		/** @noinspection PhpUnhandledExceptionInspection */
		$file = pathinfo(
			$this->creator->create(
				$name,
				$this->getMigrationsPaths()[0],
				$table,
				$create
			), PATHINFO_FILENAME
		);

		$symfonyStyle->comment("Created Migration: $file");
	}
}
