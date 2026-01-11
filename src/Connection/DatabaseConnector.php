<?php

declare(strict_types=1);

namespace Akrista\Sequel\Connection;

/**
 * Class DatabaseConnector
 */
final class DatabaseConnector
{
    public $connection;

    /**
     * @param  ?string  $database  Database name
     */
    public function getConnection(?string $database = null): MySqlConnection|PostgresConnection|SQLiteConnection
    {
        $className = match (config('sequel.database.connection')) {
            'mysql' => 'MySqlConnection',
            'pgsql' => 'PostgresConnection',
            'sqlite' => 'SQLiteConnection',
            default => 'MySqlConnection',
        };

        $class = 'Akrista\Sequel\Connection\\' . $className;

        $this->connection = $database ? (new $class())->getConnection($database) : (new $class())->getConnection();

        return $this->connection;
    }
}
