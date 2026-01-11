<?php

declare(strict_types=1);

namespace Akrista\Sequel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class PDB
 *
 * @method static create(string $database, string $table)
 * @method statement
 */
final class PDB extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'sequeldb';
    }
}
