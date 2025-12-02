<?php

namespace InternetGuru\LaravelRecaptchaV3;

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Request;

class RecaptchaV3
{
    public function __construct(
        protected string $origin,
        protected string $sitekey,
        protected string $secret,
        protected ?string $locale,
        protected HttpFactory $http,
        protected Request $request
    ) {}

    public function sitekey(): string
    {
        return $this->sitekey;
    }

    public function initJs(): string
    {
        $url = $this->origin.'/api.js?render='.$this->sitekey;
        if ($this->locale) {
            $url .= '&hl='.$this->locale;
        }

        return '<script src="'.$url.'"></script>';
    }

    public function field(string $action): string
    {
        return '<input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response-'.$action.'">';
    }

    public function script(string $action): string
    {
        return "
            <script>
                grecaptcha.ready(function() {
                    grecaptcha.execute('".$this->sitekey."', {action: '".$action."'}).then(function(token) {
                        if (document.getElementById('g-recaptcha-response-".$action."')) {
                            document.getElementById('g-recaptcha-response-".$action."').value = token;
                        }
                    });
                });
            </script>
        ";
    }

    public function livewire(string $action): string
    {
        return "
            <div x-data x-init=\"
                grecaptcha.ready(function() {
                    grecaptcha.execute('".$this->sitekey."', {action: '".$action."'})
                        .then(function(token) {
                            \$wire.set('recaptchaToken', token);
                        });
                });
            \"></div>
        ";
    }

    public function verify(?string $token, ?string $ip = null): bool
    {
        if (empty($token)) {
            return false;
        }

        $response = $this->http->asForm()->post($this->origin.'/api/siteverify', [
            'secret' => $this->secret,
            'response' => $token,
            'remoteip' => $ip ?? $this->request->ip(),
        ]);

        $data = $response->json();

        return $data['success'] ?? false;
    }
}
