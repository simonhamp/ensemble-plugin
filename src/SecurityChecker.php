<?php

namespace SimonHamp\Ensemble;

class SecurityChecker extends AbstractRemoteProcessCall
{
    protected static $checker_path;

    public static function setCheckerPath($path)
    {
        static::$checker_path = $path;
    }

    public static function getCheckerPath()
    {
        return static::$checker_path ?: realpath(__DIR__.'/../../../bin/security-checker');
    }

    protected static function buildCommand($command, $flags)
    {
        $cmd = [
            static::getCheckerPath(),
            $command,
            '--format=json',
        ];

        return $cmd;
    }
}
