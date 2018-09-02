<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Service;

use Joomla\Console\Application;
use Joomla\Console\Loader\ContainerLoader;
use Joomla\Console\Loader\LoaderInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * Console service provider
 */
final class ConsoleProvider implements ServiceProviderInterface
{
	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 */
	public function register(Container $container)
	{
		$container->share(Application::class, [$this, 'getConsoleApplicationClassService'], true);

		$container->alias(ContainerLoader::class, LoaderInterface::class)
			->share(LoaderInterface::class, [$this, 'getApplicationConsoleLoaderService'], true);
	}

	/**
	 * Get the command loader service.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  LoaderInterface
	 */
	public function getApplicationConsoleLoaderService(Container $container) : LoaderInterface
	{
		$mapping = [];

		return new ContainerLoader($container, $mapping);
	}

	/**
	 * Get the console application service.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Application
	 */
	public function getConsoleApplicationClassService(Container $container) : Application
	{
		$application = new Application(
			$container->get('config')
		);

		$application->setName('Joomla! API Documentation');
		$application->setCommandLoader($container->get(LoaderInterface::class));

		return $application;
	}
}
