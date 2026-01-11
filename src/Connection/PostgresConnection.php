<?php

declare(strict_types=1);

namespace Akrista\Sequel\Connection;

use Akrista\Sequel\Database\SequelAdapter;
use Exception;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Grammars\PostgresGrammar;
use Illuminate\Database\Query\Processors\PostgresProcessor;
use PDO;

final class PostgresConnection extends Connection
{
    /**
     * PostgresConnection constructor.
     */
    public function __construct(?string $database = null)
    {
        parent::__construct($this->getPdo($database));
    }

    public function getConnection(): static
    {
        return $this;
    }

    /**
     * Called getCustomPdo() so it doesn't override the Connection::getPdo function.
     *
     * @param  ?string  $database  Database name
     * @return PDO
     */
    public function getPdo(?string $database = null)
    {
        $connection = config('sequel.database.connection');
        $host = config('sequel.database.host');
        $port = config('sequel.database.port');
        $database = $database ?: config('sequel.database.database');

        $dsn =
            $connection.
            ':dbname='.
            $database.
            ';host='.
            $host.
            ';port='.
            $port;
        $user = config('sequel.database.username');
        $pass = config('sequel.database.password');

        return new PDO($dsn, $user, $pass);
    }

    /**
     * Return with user permissions.
     */
    public function getGrants(): array
    {
        return (array) $this->select(
            'SELECT grantee, privilege_type FROM information_schema.role_table_grants;'
        )[0];
    }

    public function getGrammar(): PostgresGrammar
    {
        return new PostgresGrammar($this);
    }

    public function getProcessor(): PostgresProcessor
    {
        return new PostgresProcessor;
    }

    /**
     * Gets information about the server.
     */
    public function getServerInfo(): array
    {
        $query =
            'select extract(epoch from current_timestamp - pg_postmaster_start_time())';

        return [
            'UPTIME' => (int) (
                $this->getPdo()
                    ->query($query)
                    ->fetch()[0]
            ),
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
        $columns = [];
        $connection = (new DatabaseConnector)->getConnection($database);
        $temp_columns = $connection->select(
            "SELECT column_name as field, data_type as type, is_nullable as null, column_default as default FROM information_schema.columns WHERE table_schema='".
            config('database.connections.pgsql.schema').
            "' AND table_name='".
            $table.
            "'"
        );
        $index = $connection->select(
            "SELECT a.attname FROM pg_index i JOIN pg_attribute a ON a.attrelid = i.indrelid AND a.attnum = ANY(i.indkey) WHERE i.indrelid = '".
            $table.
            "'::regclass AND i.indisprimary;"
        );

        foreach ($temp_columns as $key => $array) {
            if (count($index) > 0 && $array->field === $index[0]->attname) {
                $array->key = 'PRI';
                $array->default = null;
            }

            foreach ($array as $column_key => $value) {
                $columns[$key][ucfirst((string) $column_key)] = $value;
            }
        }

        return $columns;
    }

    /**
     * @param  string  $database  Database name
     * @param  string  $table  Table name
     */
    public function getTableData(string $database, string $table): array
    {
        $connection = (new DatabaseConnector)->getConnection($database);

        return $connection->select('SELECT * FROM '.$table);
    }

    /**
     * @throws Exception
     */
    public function getTablesFromDB(string $database): array
    {
        $tables = [];

        $databaseQueries = new SequelAdapter(
            config('sequel.database.connection')
        );
        $connection = (new DatabaseConnector)->getConnection($database);
        $tempTables = $connection->select(
            $databaseQueries->showTablesFrom($database)
        );
        $counter = count($tempTables);

        for ($i = 0; $i < $counter; $i++) {
            $tables[] = $tempTables[$i];
        }

        return $tables;
    }
}
