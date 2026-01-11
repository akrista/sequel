<?php

declare(strict_types=1);

namespace Akrista\Sequel\Http\Controllers;

use Akrista\Sequel\App\AppStatus;
use Akrista\Sequel\App\ControllerAction;
use Akrista\Sequel\App\FactoryAction;
use Akrista\Sequel\App\MigrationAction;
use Akrista\Sequel\App\ModelAction;
use Akrista\Sequel\App\ResourceAction;
use Akrista\Sequel\App\SeederAction;
use Akrista\Sequel\Database\DatabaseAction;
use Akrista\Sequel\Database\Query;
use Akrista\Sequel\Facades\PDB;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Class DatabaseActionController
 */
final class DatabaseActionController extends Controller
{
    /**
     * Get defaults for 'Insert new row' action form inputs.
     */
    public function getDefaultsForTable(Request $request): array
    {
        return [
            'id' => (int) PDB::create($request->database, $request->table)
                ->builder()
                ->count() + 1,
            'current_date' => Carbon::now()->format("Y-m-d\TH:i"),
        ];
    }

    /**
     * Check and return all Laravel specific assets for table (Model, Seeder, Controller etc.).
     *
     *
     * @throws Exception
     */
    public function getInfoAboutTable(string $database, string $table): array
    {
        return [
            'controller' => (new ControllerAction($database, $table))->getQualifiedName() ??
                false,
            'resource' => (new ResourceAction(
                $database,
                $table
            ))->getQualifiedName(),
            'model' => (new ModelAction($database, $table))->getQualifiedName(),
            'seeder' => (new SeederAction(
                $database,
                $table
            ))->getQualifiedName(),
            'factory' => (new FactoryAction(
                $database,
                $table
            ))->getQualifiedName(),
        ];
    }

    /**
     * Insert row in table.
     */
    public function insertNewRow(Request $request): array
    {
        return [
            'success' => (bool) (new DatabaseAction(
                $request->database,
                $request->table
            ))->insertNewRow($request->post('data')),
        ];
    }

    /**
     * Run raw SQL query.
     *
     *
     * @return array|Query
     */
    public function runSql(Request $request): array
    {
        return (new Query($request))->get();
    }

    public function import(string $database, string $table): void
    {
        //
    }

    public function export(string $database, string $table): void
    {
        //
    }

    /**
     * Get database status.
     */
    public function status(): array
    {
        return (new AppStatus())->getStatus();
    }

    /**
     * Run pending migrations.
     *
     *
     * @return int
     */
    public function runMigrations(string $database, string $table)
    {
        return (new MigrationAction($database, $table))->run();
    }

    /**
     * Reset latest migrations.
     *
     *
     * @return int
     */
    public function resetMigrations(string $database, string $table)
    {
        return (new MigrationAction($database, $table))->reset();
    }

    /**
     * Generate controller.
     *
     *
     * @throws Exception
     */
    public function generateController(string $database, string $table): string
    {
        return (new ControllerAction($database, $table))->generate();
    }

    /**
     * Generate factory.
     *
     *
     * @throws Exception
     */
    public function generateFactory(string $database, string $table): string
    {
        return (new FactoryAction($database, $table))->generate();
    }

    /**
     * Generate model.
     */
    public function generateModel(string $database, string $table): string
    {
        return (new ModelAction($database, $table))->generate();
    }

    /**
     * Generate resource.
     *
     *
     *
     * @throws Exception
     */
    public function generateResource(string $database, string $table): string
    {
        return (new ResourceAction($database, $table))->generate();
    }

    /**
     * Generate seeder.
     *
     *
     *
     * @throws Exception
     */
    public function generateSeeder(string $database, string $table): string
    {
        return (new SeederAction($database, $table))->generate();
    }

    /**
     * Run seeder.
     *
     *
     * @return int
     *
     * @throws Exception
     */
    public function runSeeder(string $database, string $table)
    {
        return (new SeederAction($database, $table))->run();
    }
}
