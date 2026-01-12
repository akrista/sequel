<?php

declare(strict_types=1);

namespace Akrista\Sequel\Database;

use Akrista\Sequel\Connection\DatabaseConnector;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Str;
use PDO;

/**
 * Class SequelDB
 */
final class SequelDB extends Model
{
    protected Builder $queryBuilder;

    private \Akrista\Sequel\Connection\MySqlConnection|\Akrista\Sequel\Connection\PostgresConnection|\Akrista\Sequel\Connection\SQLiteConnection|null $dbConnection = null;

    /**
     * @param  string  $database  Database name
     * @param  string  $table  Table name
     */
    public function create(string $database, string $table): static
    {
        $this->dbConnection = (new DatabaseConnector())->getConnection(
            $database
        );
        $tableName = $this->dbConnection->formatTableName($database, $table);
        $this->queryBuilder = new Builder(
            $this->dbConnection->getConnection(),
            $this->dbConnection->getGrammar(),
            $this->dbConnection->getProcessor()
        );

        $this->queryBuilder->from($tableName);

        return $this;
    }

    public function builder(): Builder
    {
        return $this->queryBuilder;
    }

    public function statement(array $queries): array
    {
        $queryResponse = [];

        foreach ($queries as $query) {
            if (empty($query)) {
                continue;
            }

            if (Str::startsWith(mb_strtolower((string) $query), 'select')) {
                $queryResponse[] = $this->dbConnection
                    ->getPdo()
                    ->query($query)
                    ->fetchAll(PDO::FETCH_ASSOC);
            } elseif (
                Str::startsWith(mb_strtolower((string) $query), 'update') ||
                Str::startsWith(mb_strtolower((string) $query), 'delete')
            ) {
                $queryResponse[] = $this->dbConnection
                    ->getPdo()
                    ->query($query)
                    ->rowCount();
            } else {
                $queryResponse[] = $this->dbConnection->getPdo()->query($query);
            }
        }

        return $queryResponse;
    }
}
