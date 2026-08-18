<?php

namespace Tests;

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Request;
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

    public function test_livewire_snippet_fetches_the_token_on_the_first_form_interaction()
    {
        $html = $this->enabledRecaptcha()->livewire('feedback_send');

        $this->assertStringContainsString("addEventListener('focusin', start)", $html);
        $this->assertStringContainsString("addEventListener('pointerdown', start)", $html);
        $this->assertStringContainsString("grecaptcha.execute('sitekey', {action: 'feedback_send'})", $html);
        $this->assertStringContainsString("\$wire.set('recaptchaToken', token)", $html);
    }

    public function test_livewire_snippet_does_not_execute_recaptcha_on_render()
    {
        $html = $this->enabledRecaptcha()->livewire('feedback_send');

        // The token must not be requested before the form is used, otherwise a hidden form
        // (e.g. inside a closed modal) spends a token on every page load.
        $this->assertStringContainsString('let started = false;', $html);
        $this->assertGreaterThan(
            strpos($html, 'started = true;'),
            strpos($html, 'grecaptcha.ready('),
            'reCAPTCHA must only be executed after the first interaction started the refresh loop.'
        );
    }

    public function test_livewire_snippet_starts_with_a_declaration_alpine_can_evaluate()
    {
        $html = $this->enabledRecaptcha()->livewire('feedback_send');

        // Alpine only wraps an x-init expression in an async IIFE when it starts with
        // `if (`, `let` or `const`; anything else is evaluated as a single expression.
        preg_match('/x-init="(.*?)"><\/div>/s', $html, $matches);
        $this->assertMatchesRegularExpression('/^(let|const)\s/', trim($matches[1] ?? ''));
    }

    public function test_livewire_snippet_is_empty_when_disabled()
    {
        $this->assertSame('', app(RecaptchaV3::class)->livewire('feedback_send'));
    }

    private function enabledRecaptcha(): RecaptchaV3
    {
        return new class('https://www.google.com/recaptcha', 'sitekey', 'secret', null, 0.7, app(HttpFactory::class), app(Request::class)) extends RecaptchaV3
        {
            public function isEnabled(): bool
            {
                return true;
            }
        };
    }
}
