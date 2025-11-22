<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Laravel\Scout\Console\ImportCommand;
use Modules\Appearance\Entities\Theme;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(TranslationServiceProvider::class);

        // Ensure a resolvable 'theme' binding exists for helpers like app('theme') / theme()
        if (! $this->app->bound('theme')) {
            $this->app->singleton('theme', function () {
                // Safe fallback object if DB or table is not ready yet
                $fallback = (object) [
                    'folder_path' => 'amazy',
                    'name' => 'Amazy',
                ];

                try {
                    // Only attempt DB if available and table exists
                    if (\Illuminate\Support\Facades\DB::connection()->getPdo() && Schema::hasTable('themes')) {
                        $active = Theme::where('is_active', 1)->first();
                        if ($active) {
                            return $active;
                        }
                    }
                } catch (\Throwable $e) {
                    // Swallow and use fallback during early boot / migrations
                }

                return $fallback;
            });
        }

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // if (!$this->app->runningInConsole()) {
        //     $this->commands([
        //         ImportCommand::class,
        //     ]);
        // }
        if (!Collection::hasMacro('paginate')) {
            Collection::macro('paginate',
                function ($perPage = 15, $page = null, $options = []) {
                $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
                return (new LengthAwarePaginator(
                    $this->forPage($page, $perPage), $this->count(), $perPage, $page, $options))
                    ->withPath('');
            });
        }

//        if (config('app.force_https')) {
//            URL::forceScheme('https');
//        }


        Schema::defaultStringLength(191);


        Validator::extend('check_unique_phone', function($attribute, $value, $parameters, $validator) {
            if (is_numeric($value)) {
              $data=User::where('phone',$value)->first();
              if($data){
                return false;
               }
                return true;
            }
            return true;

        });

        Paginator::useBootstrap();

    }
}
