<?php

namespace SimonHamp\Ensemble;

use Illuminate\Http\Request;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Encryption\DecryptException;

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

        try {
            $params = $this->parseParams($key);
        } catch (DecryptException $e) {
            return $this->errorResponse($e->getMessage());
        }

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
                    $result = $this->executeCommand($command, $flags);

                    return $this->encrypter->encrypt($result);
                }
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $command, $flags);
        }

        return response()->json(['payload' => $payload,]);
    }

    /**
     * @param string $command
     * @param string $flags
     * @return array|string
     * @throws \Exception
     */
    protected function executeCommand($command, $flags)
    {
        if ($command === 'outdated') {
            $flags = explode(',', $flags);
        } else {
            $flags = [];
        }

        $result = [];

        switch ($command) {
            case 'security':
                $result = SecurityChecker::getJson($command);
                break;

            case 'outdated':
            case 'licenses':
                $result = PackageChecker::getJson($command, $flags);
                break;
        }

        return $result;
    }

    protected function errorResponse($message, $command = null, $flags = null)
    {
        $payload = $this->encrypter->encrypt([
            'failure' => [
                'reason' => $message,
                'command' => $command,
                'flags' => $flags,
            ],
        ]);

        return response()->json(['payload' => $payload,], 400);
    }
}
