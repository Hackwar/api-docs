<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Command;

use Joomla\ApiDocumentation\Parser\Filesystem\DirectoryParser;
use Joomla\ApiDocumentation\Parser\Filesystem\FileParser;
use Joomla\Console\AbstractCommand;
use Joomla\Registry\Registry;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Process\Process;

/**
 * Command to parse all files for a release
 */
final class ParseFilesCommand extends AbstractCommand
{
	/**
	 * Internal tracker of stable releases for select software
	 *
	 * @const  array
	 * @todo   Make this more dynamic
	 */
	private const STABLE_RELEASES = [
		'cms' => [
			'2.5' => '2.5.28',
			'3.x' => '3.8.12',
		]
	];

	/**
	 * Execute the command.
	 *
	 * @return  integer  The exit code for the command.
	 */
	public function execute(): int
	{
		$symfonyStyle = $this->createSymfonyStyle();

		$symfonyStyle->title('Parse Files');

		$software = $this->getApplication()->getConsoleInput()->getArgument('software');
		$version  = $this->getApplication()->getConsoleInput()->getArgument('version');

		switch ($software)
		{
			case 'cms':
				if (!isset(self::STABLE_RELEASES['cms'][$version]))
				{
					$symfonyStyle->error("Unknown CMS version '$version'");

					return 1;
				}

				$softwareVersion = self::STABLE_RELEASES['cms'][$version];

				break;

			default:
				$symfonyStyle->error("Unknown software package '$software'");

				return 1;
		}

		// TODO - This block needs to be more dynamic when Framework support is added
		$joomlaDir = dirname(__DIR__, 2) . '/repos/' . $software;

		// Pull the release tags and get to our requested version
		try
		{
			$symfonyStyle->comment("Checking out version $softwareVersion for processing");

			(new Process('git fetch --tags', $joomlaDir))->mustRun();
			(new Process("git checkout $softwareVersion", $joomlaDir))->mustRun();
		}
		catch (ProcessFailedException $e)
		{
			$this->getApplication()->getLogger()->error('Could not checkout requested version', ['exception' => $e]);

			$symfonyStyle->error('Error checking out version: ' . $e->getMessage());

			return 1;
		}

		// Get the paths to process based on the version
		$majorBranch = $softwareVersion[0];

		$branchData = $this->getApplication()->get("branches.$majorBranch");

		if (!$branchData)
		{
			$symfonyStyle->error("There is no configuration for version '$softwareVersion'");

			return 1;
		}

		$branchRegistry = new Registry($branchData);

		$data = [];

		foreach ($branchRegistry->get('paths', []) as $path)
		{
			$fullPath = $joomlaDir . '/' . $path;

			if (!is_dir($fullPath))
			{
				$symfonyStyle->error("The directory `$fullPath` does not exist.");

				continue;
			}

			$symfonyStyle->comment("Processing directory `$path`");

			$data = array_merge($data, (new DirectoryParser)->parse($fullPath, $joomlaDir));
		}

		foreach ($branchRegistry->get('files', []) as $file)
		{
			$fullPath = $joomlaDir . '/' . $file;

			$symfonyStyle->comment("Processing file `$file`");

			$data = array_merge($data, (new FileParser)->parse($fullPath, $joomlaDir));
		}

		file_put_contents(dirname(__DIR__, 2) . '/data.json', json_encode($data, JSON_PRETTY_PRINT));

		$symfonyStyle->success('Data processed');

		return 0;
	}

	/**
	 * Initialise the command.
	 *
	 * @return  void
	 */
	protected function initialise()
	{
		$this->setName('parse-files');
		$this->setDescription('Parse the files for a given release');
		$this->addArgument('software', InputArgument::REQUIRED, 'The software package to process');
		$this->addArgument('version', InputArgument::REQUIRED, 'The software version to process');
	}
}
