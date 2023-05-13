<?php

namespace App\Providers;


use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use Doctrine\DBAL\Types\Type;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
    public function mapApiRoutes(){
        Route::group([
            'middleware' => ['api', 'cors'],
            'namespace' => $this->namespace,
            'prefix' => 'api',
        ], function ($router) {
             //Add you routes here, for example:
             Route::apiResource('/posts','PostController');
        });
    }
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Type::addType('enum', 'Doctrine\DBAL\Types\StringType');
        $this->registerPolicies();
        
        Gate::define('is-super', function (User $user) {
            return $user->role === 'superadmin';
        });

        Gate::define('is-guest', function (User $user) {
         
            return $user->role === 'guest';
        });
        

    }
}
