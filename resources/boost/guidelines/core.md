# Laravel reCAPTCHA v3 (internetguru/laravel-recaptchav3)

Google reCAPTCHA v3 for forms and Livewire components. laravel-common's `x-ig::form` already wires it in, so most forms need nothing. The full reference is `vendor/internetguru/laravel-recaptchav3/README.md`.

## When it is active

`RecaptchaV3::isEnabled()` is false, and verification passes, in any of these cases:
- in the console
- in the `local` and `testing` environments
- in demo mode (`app.demo`)
- for signed-in users
- for requests with a valid laravel-common HMAC signature

The keys come from `RECAPTCHAV3_SITEKEY` and `RECAPTCHAV3_SECRET`. So a missing reCAPTCHA locally or in tests is expected, not a bug. To test the enabled path, mock the service: `$this->mock(RecaptchaV3::class, fn ($mock) => $mock->shouldReceive('verify')->andReturn(true));`.

## Usage outside `x-ig::form`

- The layout loads the API once with `@recaptchaInit` in `<head>`.
- **Plain form:** `@recaptchaField('action_name')` inside the form and `@recaptchaScript('action_name')` after its fields. Validate with `'g-recaptcha-response' => [Rule::requiredIf(fn () => app(RecaptchaV3::class)->isEnabled()), new Recaptcha]`. Laravel skips a rule when its field is missing, so the `Recaptcha` rule (or the `recaptchav3` string rule) alone lets a request that omits the field through. A plain `required`, on the other hand, fails wherever reCAPTCHA is disabled and the field is not rendered.
- **Livewire:** `@recaptchaLivewire('action_name')` in the form, the `WithRecaptcha` trait on the component, and `$this->verifyRecaptcha()` at the start of the action. It throws a `ValidationException` on failure.
- Action names may contain only letters, digits, slashes and underscores (Google's rule); `x-ig::form` derives its own.
