<?php

namespace SimonHamp\Ensemble;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class PackageChecker
{
    const SHOW_ALL = 'all';
    const MINOR_ONLY = 'minor';

    protected static $cwd = './';

    public static function setCwd($cwd)
    {
        static::$cwd = $cwd;
    }

    public static function all()
    {
        return self::getJson([self::SHOW_ALL]);
    }

    public static function outdated()
    {
        return self::getJson();
    }

    public static function minor()
    {
        return self::getJson([self::MINOR_ONLY]);
    }

    private static function getJson(array $flags = [])
    {
        $process = self::createProcess($flags);

        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }

    private static function createProcess(array $flags = [])
    {
        $cmd = [
            '/usr/local/bin/composer',
            'outdated',
            '--format=json',
        ];

        if (in_array(self::SHOW_ALL, $flags)) {
            $cmd[] = '--all';
        }

        if (in_array(self::MINOR_ONLY, $flags)) {
            $cmd[] = '--minor-only';
        }

        return new Process($cmd, realpath(static::$cwd));
    }
}
