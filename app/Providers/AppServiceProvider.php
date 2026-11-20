<?php

namespace App\Providers;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Modules\Admin\Entities\Business;
use Laravel\Passport\Passport;
use App\Services\Frontend\CatalogService;
use Modules\Product\Models\Product;
use Modules\Product\Models\Stock;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();
        Paginator::useBootstrapFour();

        // Business contact info for every public website view (one query per request).
        View::composer('frontend.*', function ($view) {
            static $business = false;
            if ($business === false) {
                $business = Business::first();
            }
            $view->with('business', $business);
        });

        // Refresh the public catalog as soon as products, stock or product images change
        // (activate/deactivate, price, stock in/out, image upload or website-image choice).
        $flushCatalog = fn () => CatalogService::flush();
        foreach ([Product::class, Stock::class] as $model) {
            $model::saved($flushCatalog);
            $model::updated($flushCatalog); // also fires for ->increment()/->decrement() on sold quantities
            $model::deleted($flushCatalog);
        }
        $flushForProductMedia = function (Media $media) {
            if (in_array($media->model_type, [Product::class, \Modules\Product\Models\Company::class], true)) {
                CatalogService::flush();
            }
        };
        Media::saved($flushForProductMedia);
        Media::deleted($flushForProductMedia);

        Gate::before(function ($user, $ability) {
            return $user->hasRole('SuperAdmin') ? true : null;
        });

        // Configure Passport
        Passport::tokensExpireIn(now()->addDays(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));
    }
}
