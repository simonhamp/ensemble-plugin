<?php

namespace SimonHamp\Ensemble;

use Illuminate\Http\Request;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Cache;

class Controller
{
    protected $encrypter;

    public function __construct()
    {
        AbstractRemoteProcessCall::setCwd(base_path());

        $key = base64_decode(env('ENSEMBLE_PRIVATE_KEY'));
        $cipher = env('ENSEMBLE_CIPHER', 'AES-256-CBC');

        $this->encrypter = new Encrypter($key, $cipher);
    }

    public function ensemble(Request $request) {
        $key = $request->input('key');

        $params = $this->parseParams($key);

        $command = $params->command;

        switch ($command) {
            case 'outdated':
                $flags = $params->flags;
                break;

            default:
                $flags = 'info';
        }

        return $this->response($command, $flags);
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
    }

    protected function hasExpired($expires)
    {
        $tz = new \DateTimeZone('UTC');
        $now = new \DateTime('now', $tz);
        $timeout = new \DateTime($expires, $tz);

        return $now->diff($timeout)->invert;
    }

    protected function response($command, $flags)
    {
        try {
            $payload = Cache::remember(
                "ensemble_{$command}_{$flags}",
                env('ENSEMBLE_CACHE_TTL', 60),
                function () use ($command, $flags) {
                    if ($command == 'outdated') {
                        $flags = explode(',', $flags);
                    } else {
                        $flags = [];
                    }

                    $response = [];

                    switch ($command) {
                        case 'security':
                            $response = SecurityChecker::getJson($command);
                            break;

                        case 'outdated':
                        case 'licenses':
                            $response = PackageChecker::getJson($command, $flags);
                            break;
                    }

                    return $this->encrypter->encrypt($response);
                }
            );
        } catch (\Exception $e) {
            $payload = $this->errorResponse($e->getMessage(), $command, $flags);
        }

        return response()->json(['payload' => $payload,]);
    }

    protected function errorResponse($message, $command, $flags)
    {
        return [
            'failure' => [
                'reason' => $message,
                'command' => $command,
                'flags' => $flags,
            ],
        ];
    }
}
