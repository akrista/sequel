<?php

declare(strict_types=1);

namespace Akrista\Sequel\Http\Controllers;

use Akrista\Sequel\App\AppStatus;
use Akrista\Sequel\App\MigrationAction;
use Akrista\Sequel\Database\DatabaseTraverser;
use Akrista\Sequel\Facades\PDB;
use Akrista\Sequel\Http\Requests\SequelDatabaseRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Route;

/**
 * Class DatabaseActionController
 */
final class DatabaseController extends Controller
{
    /**
     * Qualified table name; 'database.table'
     *
     * @var string
     */
    private $qualifiedName;

    /**
     * Table name
     *
     * @var string
     */
    private $tableName;

    /**
     * Database name
     *
     * @var string
     */
    private $databaseName;

    /**
     * Holds model for given table if one exists.
     *
     * @var Model
     */
    private $model;

    /**
     * DatabaseActionController's constructor
     *
     * @param  Request|SequelDatabaseRequest  $request
     */
    public function __construct(Request|SequelDatabaseRequest $request)
    {
        $this->tableName = $request->table;
        $this->databaseName = $request->database;
        $this->qualifiedName = $request->qualifiedName;
        $this->model = $request->model;
    }

    /**
     * Get table data, table structure and its qualified name
     */
    public function getTableData(): array
    {
        // If Model exists
        if ($this->model && $this->databaseName === config('database.connections.mysql.database')) {
            $paginated = $this->model->paginate(config('sequel.pagination'));
            $paginated->setCollection(
                $paginated
                    ->getCollection()
                    ->each->setHidden([])
                    ->each->setVisible([])
            );

            return [
                'table' => $this->qualifiedName,
                'data' => $paginated,
                'structure' => app(DatabaseTraverser::class)->getTableStructure(
                    $this->databaseName,
                    $this->tableName
                ),
            ];
        }

        $data = PDB::create($this->databaseName, $this->tableName)
            ->builder()
            ->paginate(config('sequel.pagination'));

        return [
            'table' => $this->tableName,
            'structure' => app(DatabaseTraverser::class)->getTableStructure(
                $this->databaseName,
                $this->tableName
            ),
            'data' => json_decode(json_encode($data->toArray(), JSON_INVALID_UTF8_IGNORE), true),
        ];

        //
        //        if (config('database.connections.mysql.prefix')) {
        //                config(['database.connections.mysql.prefix' => '']);
        //                \Illuminate\Support\Facades\DB::purge();
        //         }
        //
        //        // Usage of the DB facade should be avoided since this uses the default config, and not the  config. @TODO refactor
        //        return [
        //             "table"     => $this->qualifiedName,
        //             "structure" => app(DatabaseTraverser::class)->getTableStructure(
        //                 $this->databaseName,
        //                 $this->tableName
        //              ),
        //             "data"      => DB::table($this->qualifiedName)->paginate(config('sequel.pagination')),
        //        ];
        //
    }

    /**
     * Find given value in given column with given operator.
     *
     * @return mixed
     */
    public function findInTable()
    {
        $column = (string) Route::current()->parameter('column');
        $queryType = (string) Route::current()->parameter('type');
        $value = (string) Route::current()->parameter('value');
        $value = $queryType === 'LIKE' ? '%' . $value . '%' : $value;

        return PDB::create($this->databaseName, $this->tableName)
            ->builder()
            ->where($column, $queryType, $value)
            ->paginate(config('sequel.pagination'));
    }

    /**
     * Get database status.
     */
    public function status(): array
    {
        return (new AppStatus())->getStatus();
    }

    /**
     * Count number of records in the given table
     */
    public function count(): array
    {
        return [
            'count' => $this->model
                ? $this->model->count()
                : PDB::create($this->databaseName, $this->tableName)
                    ->builder()
                    ->count(),
        ];
    }

    /**
     * Run pending migrations.
     */
    public function runMigrations(): int
    {
        return (new MigrationAction($this->databaseName, $this->tableName))->run();
    }

    /**
     * Reset latest migrations.
     */
    public function resetMigrations(): int
    {
        return (new MigrationAction($this->databaseName, $this->tableName))->reset();
    }
}
