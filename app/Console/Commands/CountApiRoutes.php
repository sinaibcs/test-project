<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;


class CountApiRoutes extends Command
{
    protected $signature = 'route:count-api';
    protected $description = 'Count the number of API routes';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $apiRoutes = collect(Route::getRoutes())->filter(function ($route) {
            return in_array('api', $route->gatherMiddleware());
        });

        $this->info('Number of API routes: ' . $apiRoutes->count());
    }
}