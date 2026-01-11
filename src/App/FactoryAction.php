<?php

declare(strict_types=1);

namespace Akrista\Sequel\App;

use Akrista\Sequel\Interfaces\GenerationInterface;
use Akrista\Sequel\Traits\classResolver;
use Exception;
use Illuminate\Support\Facades\Artisan;

final class FactoryAction implements GenerationInterface
{
    use classResolver;

    /**
     * ControllerAction constructor.
     */
    public function __construct(private string $database, private string $table) {}

    /**
     * Generate factory.
     *
     * @throws Exception
     */
    public function generate(): string
    {
        Artisan::call('make:factory', [
            'name' => $this->generateFactoryName($this->table),
        ]);

        $this->dumpAutoload();

        return (string) $this->getQualifiedName();
    }

    /**
     * Resolve and check seeder for table.
     *
     *
     * @throws Exception
     */
    public function checkAndGetFactoryName(): string
    {
        $factoryFile = $this->generateFactoryName($this->table);

        if (
            !file_exists(
                base_path('database/factories/' . $factoryFile . '.php')
            )
        ) {
            throw new Exception(
                $factoryFile .
                ' could not be found or your factory does not follow naming convention'
            );
        }

        return $factoryFile;
    }

    /**
     * Get fully qualified class name
     */
    public function getQualifiedName(): string|false
    {
        try {
            return $this->checkAndGetFactoryName();
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
}
