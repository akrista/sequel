<?php

declare(strict_types=1);

namespace Akrista\Sequel\App;

use Akrista\Sequel\Interfaces\GenerationInterface;
use Akrista\Sequel\Traits\classResolver;
use Exception;
use Illuminate\Support\Facades\Artisan;

final class ResourceAction implements GenerationInterface
{
    use classResolver;

    /**
     * ControllerAction constructor.
     */
    public function __construct(private string $database, private string $table) {}

    /**
     * Generate resource
     */
    public function generate(): string
    {
        Artisan::call('make:resource', [
            'name' => $this->generateResourceName($this->table),
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
            return $this->checkAndGetResourceName();
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
     * Resolve and check resource for table.
     *
     * @throws Exception
     */
    private function checkAndGetResourceName(): string
    {
        $resourceClass =
            'App\\Http\\Resources\\' .
            $this->generateResourceName($this->table);

        if (!$this->classExists($resourceClass)) {
            throw new Exception(
                $resourceClass .
                ' could not be found or your resource does not follow naming convention'
            );
        }

        return $resourceClass;
    }
}
