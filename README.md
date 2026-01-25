# Laravel Recaptcha V3

A Laravel package for Google's reCAPTCHA v3, supporting both standard Controllers and Livewire components.

## Installation

Install the package via Composer:

```bash
composer require internetguru/laravel-recaptchav3
```

## Configuration

Add your reCAPTCHA keys to your `.env` file:

```env
RECAPTCHAV3_SITEKEY=your-site-key
RECAPTCHAV3_SECRET=your-secret-key
```

Optionally, you can publish the configuration file:

```bash
php artisan vendor:publish --tag=recaptchav3-config
```

## Usage

### Initialization

Google recommends loading the reCAPTCHA API on every page to get the most context about user interactions. Add the `@recaptchaInit` directive to your main layout file, preferably in the `<head>` section.

```blade
<!-- resources/views/layouts/app.blade.php -->
<head>
    <!-- ... -->
    @recaptchaInit
</head>
```

### Standard Forms (Controllers)

1.  **Blade View**: Add the directives to your form.

    ```blade
    <form method="POST" action="/contact">
        @csrf

        <!-- 1. Add hidden input field -->
        @recaptchaField('contact_submit')

        <!-- Form fields... -->
        <input type="text" name="name">

        <button type="submit">Send</button>

        <!-- 2. Add script to handle token generation/refresh -->
        @recaptchaScript('contact_submit')
    </form>
    ```

2.  **Controller**: Validate the request.

    ```php
    use Illuminate\Http\Request;
    use InternetGuru\LaravelRecaptchaV3\Rules\Recaptcha;

    public function submit(Request )
    {
        ->validate([
            'g-recaptcha-response' => ['required', new Recaptcha],
            // OR use the string alias:
            // 'g-recaptcha-response' => ['required', 'recaptchav3'],
        ]);

        // ...
    }
    ```

### Livewire Components

1.  **Blade View**: Add the directives.

    ```blade
    <div>
        <form wire:submit.prevent="save">
            <!-- Form fields... -->
            <input type="text" wire:model="name">

            <button type="submit">Save</button>

            <!-- Add Livewire component to handle token -->
            @recaptchaLivewire('contact_save')
        </form>
    </div>
    ```

2.  **Component**: Use the trait and verify.

    ```php
    use Livewire\Component;
    use InternetGuru\LaravelRecaptchaV3\Traits\WithRecaptcha;

    class ContactForm extends Component
    {
        use WithRecaptcha;

        public $name;

        public function save()
        {
            // Verify reCAPTCHA (throws ValidationException on failure)
            $this->verifyRecaptcha();

            // ...
        }
    }
    ```

## Translations

The package comes with translations for English (en), Danish (da), and Czech (cs).

To customize the error messages, publish the translations:

```bash
php artisan vendor:publish --tag=recaptchav3-translations
```

Then edit the files in `lang/vendor/recaptchav3`.

## Testing

You can mock the `RecaptchaV3` service in your tests:

```php
use InternetGuru\LaravelRecaptchaV3\RecaptchaV3;

$this->mock(RecaptchaV3::class, function ($mock) {
    $mock->shouldReceive('verify')->andReturn(true);
});
```
## License & Commercial Terms

### Open Source License

Copyright © 2026 **Internet Guru**

This software is licensed under the [Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International (CC BY-NC-SA 4.0)](http://creativecommons.org/licenses/by-nc-sa/4.0/) license.

> **Disclaimer:** This software is provided "as is", without warranty of any kind, express or implied. In no event shall the authors or copyright holders be liable for any claim, damages or other liability.

---

### Commercial Use

The standard CC BY-NC-SA license prohibits commercial use. If you wish to use this software in a commercial environment or product, we offer **flexible commercial licenses** tailored to:

* Your company size.
* The nature of your project.
* Your specific integration needs.

**Note:** In many instances (especially for startups or small-scale tools), this may result in no fees being charged at all. Please contact us to obtain written permission or a commercial agreement.

**Contact for Licensing:** [info@internetguru.io](mailto:info@internetguru.io)

---

### Professional Services

Are you looking to get the most out of this project? We are available for:

* **Custom Development:** Tailoring the software to your specific requirements.
* **Integration & Support:** Helping your team implement and maintain the solution.
* **Training & Workshops:** Seminars and hands-on workshops for your developers.

Reach out to us at [info@internetguru.io](mailto:info@internetguru.io) — we are more than happy to assist you!
