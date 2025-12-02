<?php

namespace Tests;

use InternetGuru\LaravelRecaptchaV3\RecaptchaV3ServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            RecaptchaV3ServiceProvider::class,
        ];
    }
}
