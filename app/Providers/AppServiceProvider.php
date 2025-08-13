<?php

namespace App\Providers;

use App\Models\Beneficiary;
use App\Models\FinancialYear;
use Illuminate\Support\Collection;
use App\Services\OAuthTokenService;
use App\Observers\BeneficiaryObserver;
use Illuminate\Support\ServiceProvider;
use App\Repositories\OAuthTokenRepository;
use App\Interfaces\OAuthTokenServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Services\Global\BengaliUtilService;
use App\Interfaces\OAuthTokenRepositoryInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('bengali-util-service', function ($app) {
            return new BengaliUtilService();
        });
        $this->app->bind(OAuthTokenRepositoryInterface::class, OAuthTokenRepository::class);
        $this->app->bind(OAuthTokenServiceInterface::class, OAuthTokenService::class);
        $this->app->singleton('CurrentFinancialYear', function () {
            return FinancialYear::whereDate('start_date', '<=', now())
                                ->whereDate('end_date', '>=', now())
                                ->first();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if(env('TELESCOPE_ENV', 'production')=='production'){
            \URL::forceScheme('https');
        }
        
        //
        /**
         * Paginate a standard Laravel Collection.
         *
         * @param int $perPage
         * @param int $total
         * @param int $page
         * @param string $pageName
         * @return array
         */
        Collection::macro('paginate', function ($perPage, $page = null, $pageName = 'page') {
            $page = $page ?: LengthAwarePaginator::resolveCurrentPage($pageName);
           // $perPage = $perPage ?: 15;

            return new LengthAwarePaginator(
                $this->forPage($page, $perPage),
                $this->count(),
                $perPage,
                $page,
                [
                    'path' => LengthAwarePaginator::resolveCurrentPath(),
                    'pageName' => $pageName,
                ]
            );
        });
        Beneficiary::observe(BeneficiaryObserver::class);
    }
}
