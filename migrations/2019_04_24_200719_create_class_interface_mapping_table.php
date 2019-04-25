<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

use Illuminate\Database\Schema\Blueprint;
use Joomla\ApiDocumentation\Database\Migrations\Migration;

/**
 * Create the class_interface mapping table
 */
class CreateClassInterfaceMappingTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return  void
	 */
	public function up()
	{
		$this->getSchemaBuilder()->create(
			'class_interface',
			function (Blueprint $table)
			{
				$table->integer('class_id')->unsigned()->index();
				$table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade');
				$table->integer('interface_id')->unsigned()->index();
				$table->foreign('interface_id')->references('id')->on('interfaces')->onDelete('cascade');
			}
		);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return  void
	 */
	public function down()
	{
		$this->getSchemaBuilder()->dropIfExists('class_interface');
	}
}
