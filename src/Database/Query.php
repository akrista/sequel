<?php

declare(strict_types=1);

namespace Akrista\Sequel\Database;

use Akrista\Sequel\Facades\PDB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Class Query
 */
final class Query
{
    /**
     * @var mixed
     */
    private $database;

    /**
     * @var mixed
     */
    private $table;

    private readonly array $queries;

    /**
     * Query constructor.
     */
    public function __construct(Request $request)
    {
        $this->database = $request->database;
        $this->table = $request->table;
        $this->queries = $this->collector($request->all('query'));
    }

    /**
     * General purpose query runner with all information packed in response
     */
    public function get(): array
    {
        $arr = [];

        $iteration = 0;
        foreach ($this->queries as $query) {
            $query = mb_trim((string) $query);
            $type = $this->getType($query);
            $results =
                $type === 'dql'
                    ? Arr::collapse($this->run($query))
                    : [[$this->run($query)]];
            $rows = $this->getRows($results, $type === 'dql');

            $arr[$iteration] = [
                'query' => $query,
                'columns' => $rows,
                'data' => $type === 'dql' ? $results : $results[0],
                'type' => $type,
            ];

            $iteration++;
        }

        return $arr;
    }

    /**
     * Run query
     *
     *
     * @return mixed
     */
    public function run(string $query)
    {
        $realQuery = [$query];

        try {
            return PDB::create($this->database, $this->table)->statement(
                $realQuery
            );
        } catch (Exception $exception) {
            return $exception;
        }
    }

    /**
     * Collect array with all queries
     */
    private function collector(array $queryString): array
    {
        $queries = explode(';', (string) $queryString['query']);

        foreach ($queries as $key => $query) {
            if (!$query || ($query === '' || $query === '0') || $query === '') {
                unset($queries[$key]);
            }
        }

        return $queries;
    }

    /**
     * Get simple query type
     */
    private function getType(string $query): string|false
    {
        $str = mb_strtolower($query);
        $types = (object) [
            'ddl' => ['create', 'alter', 'rename', 'drop', 'truncate'],
            'dml' => ['insert', 'delete', 'update', 'lock', 'merge'],
            'dcl' => ['grant', 'revoke'],
            'dql' => ['select'],
        ];

        return match ($str) {
            Str::contains($str, $types->ddl) => 'ddl',
            Str::contains($str, $types->dml) => 'dml',
            Str::contains($str, $types->dcl) => 'dcl',
            Str::contains($str, $types->dql) => 'dql',
            default => false,
        };
    }

    /**
     * Get key names of results
     *
     *
     * @return array|bool
     */
    private function getRows(array $results, bool $select = true): array
    {
        $keys = [];

        if (!$select) {
            $keys[] = [
                'Key' => 'Rows affected',
                'Field' => 'Rows affected',
                'Type' => '...',
            ];
        }

        if ($select && $results && $results !== []) {
            $sample = (array) $results[0];

            foreach (array_keys($sample) as $key) {
                $keys[] = [
                    'Key' => $key,
                    'Field' => $key,
                    'Type' => $key,
                ];
            }
        }

        return $keys;
    }
}
