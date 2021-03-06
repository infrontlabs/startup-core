<?php

namespace Infrontlabs\Startup;

use Hashids\Hashids;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Infrontlabs\Startup\Models\Account;
use Infrontlabs\Startup\Models\ConfirmationToken;

class StartupServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../migrations');
        $this->loadViewsFrom(__DIR__ . '/../views', 'startup');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        Route::model('confirmation_token', ConfirmationToken::class);

        $this->app->singleton('currentAccount', function () {
            return optional(auth()->user())->currentAccount;
        });

        $this->app->singleton('hashid', function () {
            return new Hashids(config('app.name'), 10);
        });

        Route::bind('account', function ($value) {
            $id = collect(app('hashid')->decode($value))->first();
            return Account::find($id) ?? abort(404);
        });

        View::composer('*', function ($view) {
            $view->with('account', optional(auth()->user())->currentAccount);
        });

        View::composer('startup::account.team.index', function ($view) {
            $canmanageteams = optional(auth()->user())->can('admin account');
            $view->with('canmanageteams', $canmanageteams);
        });

        Request::macro('account', function () {
            return optional(auth()->user())->currentAccount;
        });

    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
