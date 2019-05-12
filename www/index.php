<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

require dirname(__DIR__) . '/vendor/autoload.php';

(new \Joomla\ApiDocumentation\Kernel\WebKernel)->run();
