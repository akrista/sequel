<?php

declare(strict_types=1);

namespace Akrista\Sequel\App;

use Akrista\Sequel\Connection\DatabaseConnector;
use Illuminate\Database\Connection;

/**
 * Class AppStatus
 */
final class AppStatus
{
    /**
     * Holds Sequel's database connection.
     */
    private readonly \Akrista\Sequel\Connection\MySqlConnection|\Akrista\Sequel\Connection\PostgresConnection|\Akrista\Sequel\Connection\SQLiteConnection $connection;

    /**
     * AppStatus constructor.
     */
    public function __construct()
    {
        $this->connection = (new DatabaseConnector())->getConnection();
    }

    public function getStatus(): array
    {
        return [
            'migrations' => (new MigrationAction('', ''))->pending(),
            'serverInfo' => $this->serverInfo(),
            'permissions' => $this->userPermissions(),
        ];
    }

    /**
     * Check database permissions for current user.
     */
    public function userPermissions(): array
    {
        $grants = $this->connection->getGrants();
        $privs = (string) array_values($grants)[0];
        $permissions = [];

        // If anyone seeing this has a better way of checking this, be my guest!
        $permissions['SELECT'] = str_contains($privs, 'SELECT');
        $permissions['INSERT'] = str_contains($privs, 'INSERT');
        $permissions['UPDATE'] = str_contains($privs, 'UPDATE');
        $permissions['DELETE'] = str_contains($privs, 'DELETE');
        $permissions['FILE'] = str_contains($privs, 'FILE');
        $permissions['CREATE'] = str_contains($privs, 'CREATE');
        $permissions['DROP'] = str_contains($privs, 'DROP');
        $permissions['ALTER'] = str_contains($privs, 'ALTER');
        $permissions['HAS_ALL'] = true;

        // Check if user has all needed permissions
        foreach ($permissions as $val) {
            if ($val === false) {
                $permissions['HAS_ALL'] = false;
            }
        }

        return $permissions;
    }

    /**
     * Get server info.
     */
    private function serverInfo(): array
    {
        return $this->connection->getServerInfo();
    }
}
