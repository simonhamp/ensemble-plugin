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

    /**
     * Run the specified command, returning its output as JSON.
     *
     * @param $command
     * @param array $flags
     * @return string
     * @throws \Exception
     */
    public static function getJson($command, array $flags = [])
    {
        $process = static::createProcess($command, $flags);

        if (! $process instanceof Process) {
            throw new \Exception($process);
        }

        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }

    /**
     * @param string $command
     * @param array $flags
     * @return Process
     * @throws \Exception
     */
    protected static function createProcess($command, array $flags = [])
    {
        if (! static::canRunCommand($command, $flags)) {
            throw new \Exception("Command '{$command}' cannot be executed: Failed prerequisite check.");
        }

        $command = static::buildCommand($command, $flags);

        return new Process($command, realpath(static::$cwd));
    }

    /**
     * Check any command prerequisites to determine if the command can be successfully executed.
     *
     * @param string $command
     * @param array $flags
     * @return bool
     */
    protected static function canRunCommand($command, $flags)
    {
        return true;
    }

    abstract protected static function buildCommand($command, $flags);
}
