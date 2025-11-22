<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Modules\GeneralSetting\Entities\GeneralSetting;

class GeneralSettingsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Bind a globally accessible general_setting instance
        if (! $this->app->bound('general_setting')) {
            $this->app->singleton('general_setting', function () {
                $fallback = (object) [
                    'company_name' => config('app.name', 'Laravel'),
                    'email' => env('MAIL_FROM_ADDRESS'),
                    'phone' => null,
                    'favicon' => null,
                    'logo' => null,
                    'language_code' => 'en',
                    'currency' => null,
                    'currency_code' => null,
                    'price_with_vat' => false,
                    'product_report' => false,
                    'lazyload' => 0,
                    'country_id' => null,
                    'country_name' => null,
                    'city_id' => null,
                    'dateFormat' => (object) ['format' => 'Y-m-d'],
                ];

                try {
                    if (\Illuminate\Support\Facades\DB::connection()->getPdo() && Schema::hasTable('general_settings')) {
                        $settings = GeneralSetting::query()->first();
                        if ($settings) {
                            return $settings;
                        }
                    }
                } catch (\Throwable $e) {
                    // During early boot / migrations, fall back quietly
                }

                return $fallback;
            });
        }
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
       // Nothing additional for now
    }
}
