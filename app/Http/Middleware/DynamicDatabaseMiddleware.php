<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class DynamicDatabaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $business = auth()->user()->business;

        if (isset($business)) {
            $connectionData = config('externaldb.' . $business);

            if (!$connectionData) {
                return response()->json(['error' => 'Business configuration not found'], 404);
            }

            try {
                // Reset the connection
                  // DB::purge('mysql');

                // Set new connection configuration
                Config::set('database.connections.dbconfig', [
                    'driver' => 'mysql',
                    'host' => $connectionData['secondary_server_name'],
                    'database' => $connectionData['secondary_database_name'],
                    'username' => $connectionData['secondary_server_username'],
                    'password' => $connectionData['secondary_database_password'],
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'prefix' => '',
                ]);

                 //********************  Set SQL Server connection configuration***************************
                //  Config::set('database.connections.dbconfig', [
                //     'driver' => 'sqlsrv',
                //     'host' => $connectionData['secondary_server_name'],
                //     'port' => $connectionData['secondary_server_port'] ?? 1433,
                //     'database' => $connectionData['secondary_database_name'],
                //     'username' => $connectionData['secondary_server_username'],
                //     'password' => $connectionData['secondary_database_password'],
                //     'charset' => 'utf8',
                //     'prefix' => '',
                //     'prefix_indexes' => true,
                // ]);

                //********************  Attempt the connection to verify it works ***************************
                        // $connection = DB::connection('mysql');
                        // $connection->getPdo(); // Test connection

                //********************  Example query (remove or replace for production)***************************
                        // $users = $connection->table('users')->get();

              
               
            } catch (\Exception $e) {
                return response()->json(['error' => 'Database connection failed: ' . $e->getMessage()], 500);
            }
        }

        return $next($request);
    }
}
