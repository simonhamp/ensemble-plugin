<?php

namespace SimonHamp\Ensemble;

use Illuminate\Http\Request;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Cache;
use SimonHamp\Ensemble\PackageChecker;
use Illuminate\Support\ServiceProvider;

class EnsembleServiceProvider extends ServiceProvider
{
    protected $encrypter;

    public function register()
    {
        if (! env('ENSEMBLE_ENABLED', false)) {
            return;
        }

        PackageChecker::setCwd(base_path());

        $key = base64_decode(env('ENSEMBLE_PRIVATE_KEY'));
        $cipher = env('ENSEMBLE_CIPHER', 'AES-256-CBC');

        $this->encrypter = new Encrypter($key, $cipher);

        $this->registerRoutes();
    }

    protected function registerRoutes()
    {
        $this->app['router']->post('ensemble', function (Request $request) {
            $key = $request->input('key');

            $params = $this->parseParams($key);

            return $this->payload(
                "ensemble_{$params->packages}",
                function () use ($params) {
                    return PackageChecker::{$params->packages}();
                }
            );
        });
    }

    protected function parseParams($key)
    {
        return tap(
            json_decode($this->encrypter->decrypt($key)),
            function ($payload) {
                $this->checkPayload($payload);
            }
        );
    }

    protected function checkPayload($payload)
    {
        if ($this->hasExpired($payload->expires)) {
            throw new \Exception('Key has expired');
        }

        if ($this->isInvalidMethod($payload->packages)) {
            throw new \Exception('Invalid method');
        }
    }

    protected function hasExpired($expires)
    {
        $tz = new \DateTimeZone('UTC');
        $now = new \DateTime('now', $tz);
        $timeout = new \DateTime($expires, $tz);

        return $now->diff($timeout)->invert;
    }

    protected function isInvalidMethod($method)
    {
        return ! in_array($method, ['all', 'outdated', 'minor']);
    }

    protected function payload($key, $callback)
    {
        $payload = Cache::remember($key, 1440, function () use ($callback) {
            return $this->encrypter->encrypt($callback());
        });

        return response()->json([
            'payload' => $payload,
        ]);
    }
}
