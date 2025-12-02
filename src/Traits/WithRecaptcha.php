<?php

namespace InternetGuru\LaravelRecaptchaV3\Traits;

use Illuminate\Validation\ValidationException;
use InternetGuru\LaravelRecaptchaV3\RecaptchaV3;

trait WithRecaptcha
{
    public $recaptchaToken;

    public function verifyRecaptcha(?string $action = null): void
    {
        $recaptcha = app(RecaptchaV3::class);

        if (! $recaptcha->verify($this->recaptchaToken)) {
            throw ValidationException::withMessages([
                'recaptchaToken' => ['ReCAPTCHA verification failed.'],
            ]);
        }
    }

    public function rules()
    {
        return [
            'recaptchaToken' => ['required', 'recaptchav3'],
        ];
    }
}
