<?php

declare(strict_types=1);

namespace Akrista\Sequel\App;

use Akrista\Sequel\Interfaces\GenerationInterface;
use Akrista\Sequel\Traits\classResolver;
use Exception;
use Illuminate\Support\Facades\Artisan;

final class ControllerAction implements GenerationInterface
{
    use classResolver;

    /**
     * ControllerAction constructor.
     */
    public function __construct(private string $database, private string $table) {}

    /**
     * Generate controller
     *
     * @throws Exception
     */
    public function generate(): string
    {
        Artisan::call('make:controller', [
            'name' => $this->generateControllerName($this->table),
        ]);

        $this->dumpAutoload();

        return (string) $this->getQualifiedName();
    }

    /**
     * Get fully qualified class name
     */
    public function getQualifiedName(): string|false
    {
        try {
            return $this->checkAndGetControllerName();
        } catch (Exception) {
            return false;
        }
    }

    /**
     * Get class name
     */
    public function getClassname(): string
    {
        $arr = explode('\\', (string) $this->getQualifiedName());
        $count = count($arr);

        return $arr[$count - 1];
    }

    /**
     * Get class namespace
     */
    public function getNamespace(): string
    {
        $arr = explode('\\', (string) $this->getQualifiedName());
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
     * Resolve and check controller for table
     *
     * @throws Exception
     */
    private function checkAndGetControllerName(): string
    {
        $controllerClass =
            'App\\Http\\Controllers\\' .
            $this->generateControllerName($this->table);

        if (!$this->classExists($controllerClass)) {
            throw new Exception(
                $controllerClass .
                ' could not be found or your controller does not follow naming convention'
            );
        }

        return $controllerClass;
    }
}
