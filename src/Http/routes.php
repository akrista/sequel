<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/**
 * |-----------------------------------------
 * |  Sequel Web Routes /sequel or via config.
 * |-----------------------------------------
 * |
 * | Separate from web route to avoid user configured path messing up the Sequel-API.
 * |
 */
Route::namespace("Akrista\Sequel\Http\Controllers")
    ->middleware(config('sequel.middleware'))
    ->prefix(config('sequel.path'))
    ->name('sequel.')
    ->group(function (): void {
        Route::get('/', 'SequelController@index')->name('index');
    });

/**
 * |-----------------------------------------
 * |  Sequel API Routes /sequel-api
 * |-----------------------------------------
 * |
 * | Separate from web route to avoid user configured path messing up the Sequel-API.
 * |
 */
Route::namespace("Akrista\Sequel\Http\Controllers")
    ->middleware(config('sequel.middleware'))
    ->prefix('sequel-api')
    ->name('sequel.')
    ->group(function (): void {
        // Get database status, includes number of migrations, avg. queries per second, open tables etc.
        Route::get('status', 'DatabaseActionController@status');

        // Get latest Sequel version
        Route::get('version', 'SequelController@version');

        // Update Sequel to latest version
        Route::post('update', 'SequelController@autoUpdate');

        // Database related routes
        Route::prefix('database')->group(function (): void {
            // Default data retrieval
            Route::get('get/{database}/{table}', 'DatabaseController@getTableData');
            Route::get('count/{database}/{table}', 'DatabaseController@count');
            Route::get('find/{database}/{table}/{column}/{type}/{value}', 'DatabaseController@findInTable');

            // MigrationAction, run or reset
            Route::get('migrations/run', 'DatabaseActionController@runMigrations');
            Route::get('migrations/reset', 'DatabaseActionController@resetMigrations');

            // Get information related to management functionality, ex. has model/factory/seeder etc.
            Route::get('info/{database}/{table}', 'DatabaseActionController@getInfoAboutTable');

            // Get default values for new row form, ex. next AI-ID, date-times etc.
            Route::get('defaults/{database}/{table}', 'DatabaseActionController@getDefaultsForTable');

            // Insert new row
            Route::post('insert/{database}/{table}', 'DatabaseActionController@insertNewRow');

            // Controller Actions
            Route::get('controller/{database}/{table}/generate', 'DatabaseActionController@generateController');

            // Factory Actions
            Route::get('factory/{database}/{table}/generate', 'DatabaseActionController@generateFactory');

            // Model Actions
            Route::get('model/{database}/{table}/generate', 'DatabaseActionController@generateModel');

            // Resource Actions
            Route::get('resource/{database}/{table}/generate', 'DatabaseActionController@generateResource');

            // Seeder Actions
            Route::get('seeder/{database}/{table}/generate', 'DatabaseActionController@generateSeeder');
            Route::get('seeder/{database}/{table}/run', 'DatabaseActionController@runSeeder');

            // Raw SQL Query
            Route::post('sql/{database}/{table}/run', 'DatabaseActionController@runSql');
        });
    });
