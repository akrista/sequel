<?php

declare(strict_types=1);

namespace Akrista\Sequel\Connection;

use Akrista\Sequel\Database\SequelAdapter;
use Exception;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Grammars\SQLiteGrammar;
use Illuminate\Database\Query\Processors\SQLiteProcessor;
use PDO;

final class SQLiteConnection extends Connection
{
    private readonly Connection $connection;

    /**
     * SQLiteConnection constructor.
     */
    public function __construct()
    {
        parent::__construct($this->getPdo());
        $this->connection = new Connection($this->getPdo());
    }

    /**
     * Get this.
     */
    public function getConnection(): self
    {
        return $this;
    }

    /**
     * Get PDO.
     */
    public function getPdo(): PDO
    {
        $connectionName = config('sequel.database.connection', 'sqlite');
        $database = config("database.connections.{$connectionName}.database");

        // Handle relative and absolute paths
        if ($database !== ':memory:' && !str_starts_with((string) $database, '/')) {
            $database = database_path($database);
        }

        $dsn = 'sqlite:' . $database;

        return new PDO($dsn);
    }

    /**
     * Return with user permissions (SQLite doesn't have grants)
     */
    public function getGrants(): array
    {
        return [
            'SQLite does not support user grants.',
        ];
    }

    /**
     * Get grammar.
     */
    public function getGrammar(): SQLiteGrammar
    {
        return new SQLiteGrammar($this);
    }

    /**
     * Get processor.
     */
    public function getProcessor(): SQLiteProcessor
    {
        return new SQLiteProcessor();
    }

    /**
     * Gets information about the server.
     */
    public function getServerInfo(): array
    {
        return [
            'SQLite Version' => $this->getPdo()->getAttribute(PDO::ATTR_SERVER_VERSION),
        ];
    }

    /**
     * @param  string  $database  Database name
     * @param  string  $table  Table name
     */
    public function formatTableName(string $database, string $table): string
    {
        return $table;
    }

    /**
     * @param  string  $database  Database name
     * @param  string  $table  Table name
     */
    public function getTableStructure(string $database, string $table): array
    {
        $result = $this->select(sprintf('PRAGMA table_info(%s)', $table));
        
        // Normalize SQLite PRAGMA output to match MySQL SHOW COLUMNS format for frontend compatibility
        return array_map(function ($column) {
            return (object) [
                'Field' => $column->name,
                'Type' => $column->type,
                'Null' => $column->notnull ? 'NO' : 'YES',
                'Key' => $column->pk ? 'PRI' : '',
                'Default' => $column->dflt_value,
                'Extra' => $column->pk ? 'auto_increment' : '',
            ];
        }, $result);
    }

    /**
     * @param  string  $database  Database name
     * @param  string  $table  Table name
     */
    public function getTableData(string $database, string $table): array
    {
        return $this->connection->select(sprintf('SELECT * FROM "%s"', $table));
    }

    /**
     * @throws Exception
     */
    public function getTablesFromDB(string $database): array
    {
        $databaseQueries = new SequelAdapter(
            config('sequel.database.connection')
        );

        return $this->select($databaseQueries->showTablesFrom($database));
    }
}
