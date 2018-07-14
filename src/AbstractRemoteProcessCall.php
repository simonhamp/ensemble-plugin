<?php

namespace SimonHamp\Ensemble;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

abstract class AbstractRemoteProcessCall
{
    protected static $cwd = './';

    public static function setCwd($cwd)
    {
        static::$cwd = $cwd;
    }

    public static function getJson($command, array $flags = [])
    {
        $process = static::createProcess($command, $flags);

        if (! $process instanceof Process) {
            return static::errorResponse($process, $command, $flags);
        }

        $process->run();

        if (! $process->isSuccessful()) {
            $e = new ProcessFailedException($process);
            return static::errorResponse($e->getMessage(), $command, $flags);
        }

        return $process->getOutput();
    }

    protected static function createProcess($command, array $flags = [])
    {
        try {
            static::canRunCommand($command, $flags);
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        $command = static::buildCommand($command, $flags);

        return new Process($command, realpath(static::$cwd));
    }

    protected static function canRunCommand($command, $flags)
    {
        return true;
    }

    protected static function errorResponse($message, $command, $flags)
    {
        return json_encode([
            'failure' => [
                'reason' => $message,
                'command' => $command,
                'flags' => $flags,
            ],
        ]);
    }

    abstract protected static function buildCommand($command, $flags);
}
