<?php

declare(strict_types=1);

namespace Protoqol\Sequel\Tests\Unit\Classes\Database;

use Exception;
use Protoqol\Sequel\Database\SequelAdapter;
use Protoqol\Sequel\Tests\TestCase;

/**
 * Class SequelAdapterTest
 */
final class SequelAdapterTest extends TestCase
{
    public function test_show_databases_gets_proper_command_for_mysql(): void
    {
        // force config
        config(['database.connections.mysql.driver' => 'mysql']);

        $adapter = new SequelAdapter('mysql');
        $this->assertEquals('SHOW DATABASES;', $adapter->showDatabases());
    }

    public function test_show_databases_gets_proper_command_for_pgsql(): void
    {
        // force config
        config(['database.connections.pgsql.driver' => 'pgsql']);

        $adapter = new SequelAdapter('pgsql');
        $this->assertEquals('SELECT datname FROM pg_database WHERE datistemplate = false;', $adapter->showDatabases());
    }

    public function test_show_databases_throws_exception_for_unsupported(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Selected invalid or unsupported database driver');

        // Force config
        config(['database.connections.my-test-here.driver' => 'unsupported-driver']);

        $adapter = new SequelAdapter('my-test-here');
        $adapter->showDatabases();
    }
}
