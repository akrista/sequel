<?php

declare(strict_types=1);

namespace Akrista\Sequel\Http\Controllers;

use Akrista\Sequel\Database\DatabaseTraverser;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Lang;

/**
 * Class SequelController
 */
final class SequelController extends Controller
{
    /**
     * Get first entry data
     */
    public function index(): Renderable
    {
        $databaseData = (object) app(DatabaseTraverser::class)->getAll();

        return view('Sequel::main', [
            'env' => [
                'connection' => config('sequel.database.connection'),
                'database' => config('sequel.database.database'),
                'host' => config('sequel.database.host'),
                'port' => config('sequel.database.port'),
                'user' => config('sequel.database.username'),
                'baseUrl' => config('sequel.baseUrl'),
            ],
            'data' => [
                'collection' => $databaseData->collection,
                'flatTableCollection' => $databaseData->flatTableCollection,
            ],
            'lang' => Lang::get(
                'Sequel::lang',
                [],
                (string) config('sequel.locale')
            ),
        ]);
    }

    /**
     * Auto update Sequel.
     */
    public function autoUpdate(Request $request): array
    {
        $newestVersion = $request->post('newest_version');

        $script = [
            'cd ' . base_path(),
            sprintf('composer require akrista/sequel:%s 2>&1', $newestVersion),
            'php artisan sequel:update 2>&1',
        ];

        $prepared = implode(' && ', $script);
        exec($prepared, $out, $return);

        return [
            'log' => $out,
            'return' => $return,
            'script' => $prepared,
        ];
    }
}
