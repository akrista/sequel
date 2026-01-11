<?php

declare(strict_types=1);

namespace Akrista\Sequel\Http\Middleware;

use Akrista\Sequel\Connection\DatabaseConnector;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

/**
 * Class Authorised
 */
final class Authorised
{
    /**
     * Handle an incoming request.
     * Checks if Sequel is enabled and has a valid database connection.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! $this->configurationCheck()->enabled) {
            return response('', 404);
        }

        if (! $this->databaseConnectionCheck()->connected) {
            return response()->view(
                'Sequel::error',
                [
                    'error_detailed' => $this->databaseConnectionCheck()
                        ->detailed,
                    'http_code' => 503,
                    'env' => [
                        'connection' => config('sequel.database.connection'),
                        'database' => config('sequel.database.database'),
                        'host' => config('sequel.database.host'),
                        'port' => config('sequel.database.port'),
                        'user' => config('sequel.database.username'),
                    ],
                    'lang' => Lang::get(
                        'Sequel::lang',
                        [],
                        (string) config('sequel.locale')
                    ),
                ],
                503
            );
        }

        return $next($request);
    }

    /**
     * Check connection with database
     *
     * @return object
     */
    private function databaseConnectionCheck()
    {
        try {
            $conn = (new DatabaseConnector)->getConnection();
            $connection = [
                'connected' => (bool) $conn->getPdo(),
                'detailed' => $conn->getPdo(),
            ];
        } catch (Exception $exception) {
            $connection = [
                'connected' => false,
                'detailed' => 'Could not create a valid database connection: '.$exception->getMessage(),
            ];
        }

        return (object) $connection;
    }

    /**
     * Check if Sequel is enabled and/or in development
     *
     * @return object
     */
    private function configurationCheck()
    {
        return (object) [
            'enabled' => config('sequel.enabled') && config('app.env') !== 'production',
            'detailed' => 'Sequel has been disabled.',
        ];
    }
}
