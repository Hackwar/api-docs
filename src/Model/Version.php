<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Model;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model defining a version of software.
 *
 * @property  integer                $id
 * @property  string                 $version
 * @property  Software               $software
 * @property  integer|null           $software_id
 * @property  Collection|PHPClass[]  $classes
 */
final class Version extends Model
{
	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var  boolean
	 */
	public $timestamps = false;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var  array
	 */
	protected $fillable = [
		'version',
	];

	/**
	 * Defines the relationship for a software version to the PHP classes it has.
	 *
	 * @return  HasMany
	 */
	public function classes(): HasMany
	{
		return $this->hasMany(PHPClass::class);
	}

	/**
	 * Defines the relationship for a version to the software it belongs to.
	 *
	 * @return  BelongsTo
	 */
	public function software(): BelongsTo
	{
		return $this->belongsTo(Software::class);
	}
}
