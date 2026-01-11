<?php

declare(strict_types=1);

namespace Akrista\Sequel\Traits;

use Illuminate\Support\Str;

trait classResolver
{
    /**
     * Dump composer's autoload.
     */
    public function dumpAutoload(): int
    {
        $out = [];
        $return = 0;

        exec('cd ' . base_path() . ' && composer dump-autoload', $out, $return);

        return $return;
    }

    /**
     * Check for class existence.
     */
    public function classExists(string $classname, ?array $namespaces = null): bool|string
    {
        if (!$namespaces) {
            return class_exists($classname);
        }

        foreach ($namespaces as $namespace) {
            $qualifiedClassName = $namespace . $classname;
            if (class_exists($qualifiedClassName)) {
                return $qualifiedClassName;
            }
        }

        return false;
    }

    /**
     * Resolve and return configured suffixes from the config.
     * Returns object with suffix and namespace or an empty string.
     *
     * @param  string  $generator  ex. 'model', 'controller', 'resource' etc.
     * @return object
     */
    public function configNamespaceResolver(string $generator)
    {
        $config = config('sequel.suffixes')[$generator];
        $exploded = explode('\\', (string) $config);
        $suffix = end($exploded);
        $namespace = $suffix
            ? mb_substr((string) $config, 0, -mb_strlen($suffix))
            : $config;

        return (object) [
            'suffix' => $suffix,
            'namespace' => $namespace,
        ];
    }

    /**
     * Transform table name to a SingularStudlyClassName.
     *
     *
     * @return string
     */
    public function generateClassName(string $classname)
    {
        return Str::studly(Str::singular($classname));
    }

    public function generateControllerName(string $classname): string
    {
        $config = $this->configNamespaceResolver('controller');

        return $config->namespace .
            $this->generateClassName($classname) .
            $config->suffix;
    }

    public function generateFactoryName(string $classname): string
    {
        $config = $this->configNamespaceResolver('factory');

        return $config->namespace .
            $this->generateClassName($classname) .
            $config->suffix;
    }

    public function generateModelName(string $classname): string
    {
        $config = $this->configNamespaceResolver('model');

        return app()->getNamespace() .
            $config->namespace .
            $this->generateClassName($classname) .
            $config->suffix;
    }

    public function generateResourceName(string $classname): string
    {
        $config = $this->configNamespaceResolver('resource');

        return $config->namespace .
            $this->generateClassName($classname) .
            $config->suffix;
    }

    public function generateSeederName(string $classname): string
    {
        $config = $this->configNamespaceResolver('seeder');

        return $config->namespace .
            $this->generateClassName($classname) .
            $config->suffix;
    }
}
