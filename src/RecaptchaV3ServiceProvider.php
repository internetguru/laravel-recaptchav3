<?php

namespace InternetGuru\LaravelRecaptchaV3;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class RecaptchaV3ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/config/recaptchav3.php', 'recaptchav3');

        $this->app->singleton(RecaptchaV3::class, function ($app) {
            return new RecaptchaV3(
                origin: config('recaptchav3.origin', 'https://www.google.com/recaptcha'),
                sitekey: config('recaptchav3.sitekey'),
                secret: config('recaptchav3.secret'),
                locale: config('recaptchav3.locale'),
                score: config('recaptchav3.score'),
                http: $app['Illuminate\Http\Client\Factory'],
                request: $app['request']
            );
        });
    }

    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/lang', 'recaptchav3');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/config/recaptchav3.php' => config_path('recaptchav3.php'),
            ], 'recaptchav3-config');

            $this->publishes([
                __DIR__.'/lang' => $this->app->langPath('vendor/recaptchav3'),
            ], 'recaptchav3-translations');
        }

        Blade::directive('recaptchaInit', function () {
            return "<?php echo app(\InternetGuru\LaravelRecaptchaV3\RecaptchaV3::class)->initJs(); ?>";
        });

        Blade::directive('recaptchaField', function ($expression) {
            return "<?php echo app(\InternetGuru\LaravelRecaptchaV3\RecaptchaV3::class)->field($expression); ?>";
        });

        Blade::directive('recaptchaScript', function ($expression) {
            return "<?php echo app(\InternetGuru\LaravelRecaptchaV3\RecaptchaV3::class)->script($expression); ?>";
        });

        Blade::directive('recaptchaLivewire', function ($expression) {
            return "<?php echo app(\InternetGuru\LaravelRecaptchaV3\RecaptchaV3::class)->livewire($expression); ?>";
        });

        Validator::extend('recaptchav3', function ($attribute, $value, $parameters, $validator) {
            return app(RecaptchaV3::class)->verify($value);
        }, trans('recaptchav3::messages.failed'));
    }
}
