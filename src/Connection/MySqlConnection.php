<?php

declare(strict_types=1);

namespace Akrista\Sequel\Connection;

use Akrista\Sequel\Database\SequelAdapter;
use Exception;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Grammars\MySqlGrammar;
use Illuminate\Database\Query\Processors\MySqlProcessor;
use PDO;

final class MySqlConnection extends Connection
{
    private readonly Connection $connection;

    /**
     * MySqlConnection constructor.
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
        $connection = config('sequel.database.connection');
        $host = config('sequel.database.host');
        $port = config('sequel.database.port');
        $database = config('sequel.database.database');
        $socket = config('sequel.database.socket');

        $dsn = $connection;

        if ($socket) {
            $dsn .=
                ':unix_socket='.$socket;
        } else {
            $dsn .=
                ':host='.
                $host.
                ';port='.
                $port;
        }

        $dsn .= ';dbname='.$database;

        $user = config('sequel.database.username');
        $pass = config('sequel.database.password');

        return new PDO($dsn, $user, $pass);
    }

    /**
     * Return with user permissions
     */
    public function getGrants(): array
    {
        return (array) $this->connection->select(
            'SHOW GRANTS FOR CURRENT_USER();'
        )[0];
    }

    /**
     * Get grammar.
     */
    public function getGrammar(): MySqlGrammar
    {
        return new MySqlGrammar($this);
    }

    /**
     * Get processor.
     */
    public function getProcessor(): MySqlProcessor
    {
        return new MySqlProcessor();
    }

    /**
     * Gets information about the server.
     */
    public function getServerInfo(): array
    {
        $serverInfo = $this->getPdo()->getAttribute(PDO::ATTR_SERVER_INFO);

        $explodedServerInfo = explode('  ', (string) $serverInfo);
        $serverInfoArray = [];

        foreach ($explodedServerInfo as $attr) {
            $split = explode(': ', $attr);
            $key = mb_strtoupper(
                str_replace(' ', '_', str_replace(':', '', $split[0]))
            );
            $serverInfoArray[$key] = $split[1];
        }

        return $serverInfoArray;
    }

    /**
     * @param  string  $database  Database name
     * @param  string  $table  Table name
     */
    public function formatTableName(string $database, string $table): string
    {
        return $database.'.'.$table;
    }

    /**
     * @param  string  $database  Database name
     * @param  string  $table  Table name
     */
    public function getTableStructure(string $database, string $table): array
    {
        return $this->select(sprintf('SHOW COLUMNS FROM `%s`.`%s`', $database, $table));
    }

    /**
     * @param  string  $database  Database name
     * @param  string  $table  Table name
     */
    public function getTableData(string $database, string $table): array
    {
        return $this->connection->select(sprintf('SELECT * FROM `%s`.`%s`', $database, $table));
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
