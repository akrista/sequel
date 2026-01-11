<?php

declare(strict_types=1);

namespace Akrista\Sequel\Commands;

use Illuminate\Console\Command;

/**
 * Class UpdateCommand
 */
final class UpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sequel:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Sequel assets, re-publishing them.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Updating Sequel Resources...');

        $this->comment('Updating Sequel Service Provider...');
        $this->callSilent('vendor:publish', [
            '--provider' => \Akrista\Sequel\SequelServiceProvider::class,
            '--force' => true,
        ]);

        $this->comment('Updating Sequel Assets...');
        $this->callSilent('vendor:publish', [
            '--tag' => 'sequel-assets',
            '--force' => true,
        ]);

        $this->comment('Updating Sequel Config...');
        $this->callSilent('vendor:publish', [
            '--tag' => 'sequel-config',
            '--force' => true,
        ]);

        $this->comment('Updating Sequel Translations...');
        $this->callSilent('vendor:publish', [
            '--tag' => 'sequel-lang',
            '--force' => true,
        ]);

        $this->info('Sequel is up-to-date!');
    }
}
