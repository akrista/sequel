<?php

declare(strict_types=1);

namespace Akrista\Sequel\Interfaces;

interface GenerationInterface
{
    /**
     * GenerationInterface constructor.
     */
    public function __construct(string $database, string $table);

    /**
     * Generate $generator
     *
     * @return mixed
     */
    public function generate();

    /**
     * Get fully qualified class name
     *
     * @return mixed
     */
    public function getQualifiedName();

    /**
     * Get class name
     *
     * @return mixed
     */
    public function getClassname();

    /**
     * Get class namespace
     *
     * @return mixed
     */
    public function getNamespace();
}
