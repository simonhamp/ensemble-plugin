<?php

namespace SimonHamp\Ensemble;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class PackageChecker
{
    const SHOW_ALL = 'all';
    const MINOR_ONLY = 'minor';
    const DIRECT = 'direct';

    protected static $cwd = './';

    public static function setCwd($cwd)
    {
        static::$cwd = $cwd;
    }

    public static function getJson($command, array $flags = [])
    {
        if (! in_array($command, ['outdated', 'licenses'])) {
            throw new \Exception("Invalid command {$command}");
        }

        $process = self::createProcess($command, $flags);

        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }

    private static function createProcess($command, array $flags = [])
    {
        $cmd = [
            'vendor/bin/composer',
            $command,
            '--format=json',
        ];

        if (in_array(self::SHOW_ALL, $flags)) {
            $cmd[] = '--all';
        }

        if (in_array(self::MINOR_ONLY, $flags)) {
            $cmd[] = '--minor-only';
        }

        if (in_array(self::DIRECT, $flags)) {
            $cmd[] = '--direct';
        }

        return new Process($cmd, realpath(static::$cwd));
    }
}
