<?php

declare(strict_types=1);

namespace Akrista\Sequel\Commands;

use Illuminate\Console\Command;

/**
 * Class InstallCommand
 */
final class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sequel:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Sequel, publishing its ServiceProvider, config and assets.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->comment('Publishing Sequel Service Provider...');
        $this->callSilent('vendor:publish', [
            '--provider' => \Akrista\Sequel\SequelServiceProvider::class,
        ]);

        $this->comment('Publishing Sequel Assets...');
        $this->callSilent('vendor:publish', [
            '--tag' => 'sequel-assets',
        ]);

        $this->comment('Publishing Sequel Config...');
        $this->callSilent('vendor:publish', [
            '--tag' => 'sequel-config',
        ]);

        $this->comment('Publishing Sequel Translations...');
        $this->callSilent('vendor:publish', [
            '--tag' => 'sequel-lang',
        ]);

        $this->info('Sequel succesfully installed.');
    }
}
