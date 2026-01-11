<?php

declare(strict_types=1);

namespace Akrista\Sequel\Database;

use Exception;

/**
 * Get queries based on chosen SQL driver.
 * Class SequelAdapter
 */
final class SequelAdapter
{
    /**
     * Holds database type e.g. 'mysql', 'pgsql', 'sqlite' etc.
     */
    private readonly string $databaseType;

    public function __construct(string $connectionName)
    {
        $this->databaseType = config(
            sprintf('database.connections.%s.driver', $connectionName)
        );
    }

    /**
     * Get all tables
     *
     * @throws Exception
     */
    public function showTables(): string
    {
        return match ($this->databaseType) {
            'mysql' => 'SHOW TABLES;',
            'pgsql' => "SELECT table_name FROM information_schema.tables WHERE table_schema='" .
                config('database.connections.pgsql.schema') .
                "' ORDER BY table_name;",
            'sqlite' => 'SELECT name FROM sqlite_master WHERE type="table";',
            default => throw new Exception(
                'Selected invalid or unsupported database driver'
            ),
        };
    }

    /**
     * @return string
     *
     * @throws Exception
     */
    public function showDatabases()
    {
        return match ($this->databaseType) {
            'mysql' => 'SHOW DATABASES;',
            'pgsql' => 'SELECT datname FROM pg_database WHERE datistemplate = false;',
            'sqlite' => 'SELECT "main" as database;',
            default => throw new Exception(
                'Selected invalid or unsupported database driver'
            ),
        };
    }

    /**
     * @return string
     *
     * @throws Exception
     */
    public function showTablesFrom(string $databaseName)
    {
        return match ($this->databaseType) {
            'mysql' => 'SHOW TABLES FROM `' . $databaseName . '`;',
            'pgsql' => "SELECT table_name FROM information_schema.tables WHERE table_schema='" .
                config('database.connections.pgsql.schema') .
                "' ORDER BY table_name;",
            'sqlite' => 'SELECT name FROM sqlite_master WHERE type="table";',
            default => throw new Exception(
                'Selected invalid or unsupported database driver'
            ),
        };
    }
}
