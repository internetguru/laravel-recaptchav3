<?php

namespace Tests;

use Illuminate\Support\Facades\Blade;
use InternetGuru\LaravelRecaptchaV3\RecaptchaV3;

class RecaptchaTest extends TestCase
{
    public function test_service_is_bound()
    {
        $this->assertTrue($this->app->bound(RecaptchaV3::class));
    }

    public function test_directives_are_registered()
    {
        $directives = Blade::getCustomDirectives();
        $this->assertArrayHasKey('recaptchaInit', $directives);
        $this->assertArrayHasKey('recaptchaField', $directives);
        $this->assertArrayHasKey('recaptchaScript', $directives);
        $this->assertArrayHasKey('recaptchaLivewire', $directives);
    }

    public function test_validation_rule_is_registered()
    {
        $validator = $this->app['validator']->make(['g-recaptcha-response' => 'token'], ['g-recaptcha-response' => 'recaptchav3']);
        // It will fail because we are not mocking the HTTP request and token is invalid, but it proves the rule exists.
        // If the rule didn't exist, it would throw an exception or behave differently depending on Laravel version (usually "Method [validateRecaptchav3] does not exist").

        // Actually, let's mock the service to return true.
        $this->mock(RecaptchaV3::class, function ($mock) {
            $mock->shouldReceive('verify')->andReturn(true);
        });

        $validator = $this->app['validator']->make(['g-recaptcha-response' => 'token'], ['g-recaptcha-response' => 'recaptchav3']);
        $this->assertTrue($validator->passes());
    }
}
