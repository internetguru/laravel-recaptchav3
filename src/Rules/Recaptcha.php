<?php

namespace InternetGuru\LaravelRecaptchaV3\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use InternetGuru\LaravelRecaptchaV3\RecaptchaV3;

class Recaptcha implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $recaptcha = app(RecaptchaV3::class);

        if (! $recaptcha->verify($value)) {
            $fail(__('recaptchav3::messages.failed'), null);
        }
    }
}
