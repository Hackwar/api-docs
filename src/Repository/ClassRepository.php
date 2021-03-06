<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Repository;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Joomla\ApiDocumentation\Model\Deprecation;
use Joomla\ApiDocumentation\Model\PHPClass;
use Joomla\ApiDocumentation\Model\PHPInterface;
use Joomla\ApiDocumentation\Model\Version;

/**
 * Repository for a PHPClass model.
 */
final class ClassRepository
{
	/**
	 * Create or update a PHPClass model based on the class node of the parsed data.
	 *
	 * @param   array    $classNode  The class node to process.
	 * @param   Version  $version    The version to assign this class node to.
	 *
	 * @return  PHPClass
	 */
	public function createOrUpdateFromClassNode(array $classNode, Version $version): PHPClass
	{
		$namespace = $classNode['namespace'] === 'global' ? null : $classNode['namespace'];

		/** @var PHPClass $classModel */
		$classModel = PHPClass::with(['deprecation'])
			->whereHas(
				'version',
				function (Builder $query) use ($version)
				{
					$query->where('id', '=', $version->id);
				}
			)
			->firstOrNew(
				[
					'namespace' => $namespace,
					'shortname' => $classNode['name'],
				]
			);

		$classModel->version()->associate($version);

		$classModel->fill(
			[
				'name'        => (string) $namespace . '\\' . $classNode['name'],
				'summary'     => $classNode['docblock']['summary'] ?? '',
				'description' => $classNode['docblock']['description'] ?? '',
				'final'       => $classNode['final'],
				'abstract'    => $classNode['abstract'],
			]
		);

		// Associate the parent if it exists
		if ($classNode['extends'] !== '')
		{
			/** @var PHPClass|null $parentClass */
			$parentClass = PHPClass::query()
				->whereHas(
					'version',
					function (Builder $query) use ($version)
					{
						$query->where('id', '=', $version->id);
					}
				)
				->where('name', '=', $classNode['extends'])
				->first();

			if ($parentClass)
			{
				$classModel->parent()->associate($parentClass);
			}
		}

		// If an existing class and there is no longer a parent, break the association
		if ($classNode['extends'] === '' && $classModel->id && $classModel->parent_id)
		{
			$classModel->parent()->dissociate();
		}

		$classModel->save();

		// Associate the interfaces the class implements if they exist
		$interfaces = new Collection;

		foreach ($classNode['implements'] as $implementsInterface)
		{
			/** @var PHPInterface|null $interface */
			$interface = PHPInterface::query()
				->whereHas(
					'version',
					function (Builder $query) use ($version)
					{
						$query->where('id', '=', $version->id);
					}
				)
				->where('name', '=', $implementsInterface)
				->first();

			if ($interface)
			{
				$interfaces->add($interface);
			}
		}

		$classModel->implements()->sync($interfaces);

		// Process tags for a deprecation if one exists
		if (isset($classNode['docblock']['tags']))
		{
			foreach ($classNode['docblock']['tags'] as $tagNode)
			{
				if ($tagNode['name'] !== 'deprecated')
				{
					continue;
				}

				/** @var Deprecation $deprecationModel */
				$deprecationModel = $classModel->deprecation ?: Deprecation::make();

				$deprecationModel->fill(
					[
						'description'     => $tagNode['description'],
						'removal_version' => $tagNode['version'],
					]
				);

				$deprecationModel->deprecatable()->associate($classModel);

				$deprecationModel->save();
			}
		}

		return $classModel;
	}
}
