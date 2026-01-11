<?php

declare(strict_types=1);

namespace Akrista\Sequel\App;

use Akrista\Sequel\Connection\DatabaseConnector;
use Akrista\Sequel\Interfaces\GenerationInterface;
use Akrista\Sequel\Traits\classResolver;
use FilesystemIterator;
use Illuminate\Support\Facades\Artisan;

/**
 * Class MigrationAction
 */
final class MigrationAction implements GenerationInterface
{
    use classResolver;

    private $connection;

    /**
     * ControllerAction constructor.
     */
    public function __construct(private string $database, private string $table)
    {
        $this->connection = (new DatabaseConnector())->getConnection();
    }

    /**
     * @return int
     */
    public function run()
    {
        return Artisan::call('migrate');
    }

    /**
     * @return int
     */
    public function reset()
    {
        return Artisan::call('migrate:reset');
    }

    /**
     * Get total and pending migrations.
     */
    public function pending(): array
    {
        $migrationFileCount = iterator_count(
            new FilesystemIterator(
                database_path('migrations'),
                FilesystemIterator::SKIP_DOTS
            )
        );

        $migrationTableCount = count(
            $this->connection->select('SELECT id FROM migrations;')
        );

        $pending = $migrationFileCount - $migrationTableCount;

        return [
            'pending' => max(0, $pending),
            'total' => $migrationFileCount,
        ];
    }

    /**
     * Generate $generator
     */
    public function generate(): void
    {
        // TODO: Implement generate() method.
    }

    /**
     * Get fully qualified class name
     */
    public function getQualifiedName(): void
    {
        // TODO: Implement getQualifiedName() method.
    }

    /**
     * Get class name
     */
    public function getClassname(): void
    {
        // TODO: Implement getClassname() method.
    }

    /**
     * Get class namespace
     */
    public function getNamespace(): void
    {
        // TODO: Implement getNamespace() method.
    }
}
