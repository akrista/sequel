<?php

declare(strict_types=1);

namespace Akrista\Sequel\App;

use Akrista\Sequel\Interfaces\GenerationInterface;
use Akrista\Sequel\Traits\classResolver;
use Exception;
use Illuminate\Support\Facades\Artisan;

final class SeederAction implements GenerationInterface
{
    use classResolver;

    /**
     * ControllerAction constructor.
     */
    public function __construct(private string $database, private string $table) {}

    /**
     * Generate seeder.
     */
    public function generate(): string
    {
        Artisan::call('make:seeder', [
            'name' => $this->generateClassName($this->table) . 'Seeder',
        ]);

        $this->dumpAutoload();

        return (string) $this->getQualifiedName();
    }

    /**
     * Run seeder.
     *
     * @return int
     *
     * @throws Exception
     */
    public function run()
    {
        return Artisan::call('db:seed', [
            '--class' => $this->checkAndGetSeederName(),
            '--database' => $this->database,
        ]);
    }

    /**
     * Get fully qualified class name
     */
    public function getQualifiedName(): string|false
    {
        try {
            return $this->checkAndGetSeederName();
        } catch (Exception) {
            return false;
        }
    }

    /**
     * Get class name
     */
    public function getClassname(): false|string
    {
        $class = $this->getQualifiedName();

        if (!$class) {
            return false;
        }

        $arr = explode('\\', $class);
        $count = count($arr);

        return $arr[$count - 1];
    }

    /**
     * Get class namespace
     */
    public function getNamespace(): false|string
    {
        $class = $this->getQualifiedName();

        if (!$class) {
            return false;
        }

        $arr = explode('\\', $class);
        $count = count($arr);
        $namespace = '';

        for ($i = 0; $i < $count; $i++) {
            if ($i === $count - 1) {
                break;
            }

            $namespace .= $arr[$i] . '\\';
        }

        return $namespace;
    }

    /**
     * Resolve and check seeder for table.
     *
     *
     * @throws Exception
     */
    private function checkAndGetSeederName(): string
    {
        $seederClass = $this->generateSeederName($this->table);

        if (!$this->classExists($seederClass)) {
            throw new Exception(
                $seederClass .
                ' could not be found or your seeder does not follow naming convention'
            );
        }

        return $seederClass;
    }
}
