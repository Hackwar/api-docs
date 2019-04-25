<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Importer;

use Joomla\ApiDocumentation\Model\Version;
use Joomla\ApiDocumentation\Repository\ClassMethodRepository;
use Joomla\ApiDocumentation\Repository\ClassRepository;
use Joomla\ApiDocumentation\Repository\FunctionRepository;
use Joomla\ApiDocumentation\Repository\InterfaceMethodRepository;
use Joomla\ApiDocumentation\Repository\InterfaceRepository;

/**
 * Importer for the data dump generated by the parser process.
 */
final class ParsedDataImporter
{
	/**
	 * PHPClass model repository.
	 *
	 * @var  ClassRepository
	 */
	private $classRepository;

	/**
	 * ClassMethod model repository.
	 *
	 * @var  ClassMethodRepository
	 */
	private $classMethodRepository;

	/**
	 * PHPFunction model repository.
	 *
	 * @var  FunctionRepository
	 */
	private $functionRepository;

	/**
	 * PHPInterface model repository.
	 *
	 * @var  InterfaceRepository
	 */
	private $interfaceRepository;

	/**
	 * InterfaceMethod model repository.
	 *
	 * @var  InterfaceMethodRepository
	 */
	private $interfaceMethodRepository;

	/**
	 * Importer constructor.
	 *
	 * @param   ClassRepository            $classRepository            PHPClass model repository.
	 * @param   ClassMethodRepository      $classMethodRepository      ClassMethod model repository.
	 * @param   FunctionRepository         $functionRepository         PHPFunction model repository.
	 * @param   InterfaceRepository        $interfaceRepository        PHPInterface model repository.
	 * @param   InterfaceMethodRepository  $interfaceMethodRepository  InterfaceMethod model repository.
	 */
	public function __construct(
		ClassRepository $classRepository,
		ClassMethodRepository $classMethodRepository,
		FunctionRepository $functionRepository,
		InterfaceRepository $interfaceRepository,
		InterfaceMethodRepository $interfaceMethodRepository
	)
	{
		$this->classRepository           = $classRepository;
		$this->classMethodRepository     = $classMethodRepository;
		$this->functionRepository        = $functionRepository;
		$this->interfaceRepository       = $interfaceRepository;
		$this->interfaceMethodRepository = $interfaceMethodRepository;
	}

	/**
	 * Import the parsed data.
	 *
	 * @param   array    $data     The data to process.
	 * @param   Version  $version  The version to assign the imported data to.
	 *
	 * @return  void
	 */
	public function importData(array $data, Version $version): void
	{
		foreach ($data['files'] as $file)
		{
			foreach ($file['interfaces'] as $interface)
			{
				$interfaceModel = $this->interfaceRepository->createOrUpdateFromInterfaceNode($interface, $version);

				foreach ($interface['methods'] as $method)
				{
					$methodModel = $this->interfaceMethodRepository->createOrUpdateFromMethodNode($method, $interfaceModel);
				}
			}

			foreach ($file['classes'] as $class)
			{
				$classModel = $this->classRepository->createOrUpdateFromClassNode($class, $version);

				foreach ($class['methods'] as $method)
				{
					$methodModel = $this->classMethodRepository->createOrUpdateFromMethodNode($method, $classModel);
				}
			}

			foreach ($file['functions'] as $function)
			{
				$functionModel = $this->functionRepository->createOrUpdateFromFunctionNode($function, $version);
			}
		}
	}
}
