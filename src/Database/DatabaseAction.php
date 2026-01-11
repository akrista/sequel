<?php

declare(strict_types=1);

namespace Akrista\Sequel\Database;

use Illuminate\Support\Facades\DB;

final class DatabaseAction
{
    /**
     * DatabaseAction constructor.
     */
    public function __construct(private readonly string $database, private readonly string $table) {}

    // @TODO MOVE TO PDB::CLASS FACADE
    public function insertNewRow(array $data)
    {
        return DB::table($this->database . '.' . $this->table)->insert($data);
    }
}
