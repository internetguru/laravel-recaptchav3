<?php

namespace InternetGuru\LaravelRecaptchaV3;

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Request;
use InternetGuru\LaravelCommon\Support\Helpers;

class RecaptchaV3
{
    public function __construct(
        protected string $origin,
        protected string $sitekey,
        protected string $secret,
        protected ?string $locale,
        protected float $score,
        protected HttpFactory $http,
        protected Request $request
    ) {}

    public function sitekey(): string
    {
        return $this->sitekey;
    }

    public function isEnabled(): bool
    {
        if (app()->runningInConsole()) {
            return false;
        }
        if (Helpers::verifyRequestSignature(request())) {
            return false;
        }
        if (app()->environment('local')) {
            return false;
        }
        if (app()->environment('testing')) {
            return false;
        }
        if (config('app.demo', false)) {
            return false;
        }
        if (auth()->check()) {
            return false;
        }

        return true;
    }

    public function initJs(): string
    {
        if (! $this->isEnabled()) {
            return '';
        }

        $url = $this->origin.'/api.js?render='.$this->sitekey;
        if ($this->locale) {
            $url .= '&hl='.$this->locale;
        }

        return '<script src="'.$url.'"></script>';
    }

    public function field(string $action): string
    {
        if (! $this->isEnabled()) {
            return '';
        }

        return '<input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response-'.$action.'">';
    }

    public function script(string $action): string
    {
        if (! $this->isEnabled()) {
            return '';
        }

        return "
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var input = document.getElementById('g-recaptcha-response-".$action."');
                    if (input) {
                        grecaptcha.ready(function() {
                            var refresh = function() {
                                grecaptcha.execute('".$this->sitekey."', {action: '".$action."'}).then(function(token) {
                                    input.value = token;
                                });
                            };
                            refresh();
                            setInterval(refresh, 100000);
                        });
                    }
                });
            </script>
        ";
    }

    /**
     * Alpine snippet keeping the `recaptchaToken` property of a Livewire component fresh.
     *
     * The token is fetched on the first interaction with the surrounding form instead of on
     * render, so a form that is never used (e.g. one inside a closed modal) does not spend a
     * token on every page load, and a form revealed client-side still has its token in place
     * before it is submitted.
     */
    public function livewire(string $action): string
    {
        if (! $this->isEnabled()) {
            return '';
        }

        return "
            <div x-data x-init=\"
                let host = \$el.closest('form') || \$el.parentElement || \$el;
                let started = false;
                const start = function () {
                    if (started) {
                        return;
                    }
                    started = true;
                    grecaptcha.ready(function () {
                        const refresh = function () {
                            grecaptcha.execute('".$this->sitekey."', {action: '".$action."'})
                                .then(function (token) {
                                    \$wire.set('recaptchaToken', token);
                                });
                        };
                        refresh();
                        setInterval(refresh, 100000);
                    });
                };
                host.addEventListener('focusin', start);
                host.addEventListener('pointerdown', start);
            \"></div>
        ";
    }

    public function verify(?string $token, ?string $ip = null): bool
    {
        if (! $this->isEnabled()) {
            return true;
        }

        if (empty($token)) {
            return false;
        }

        $response = $this->http->asForm()->post($this->origin.'/api/siteverify', [
            'secret' => $this->secret,
            'response' => $token,
            'remoteip' => $ip ?? $this->request->ip(),
        ]);

        $data = $response->json();

        if (! ($data['success'] ?? false)) {
            return false;
        }

        if (isset($data['score']) && $data['score'] < $this->score) {
            return false;
        }

        return true;
    }
}
