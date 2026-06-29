<?php

namespace App\Helpers;

use Symfony\Component\Process\Process;

class VersionHelper
{
    private static ?string $displayResolved = null;
    private static ?string $fullResolved = null;

    public static function display(): string
    {
        if (self::$displayResolved !== null) {
            return self::$displayResolved;
        }

        return self::$displayResolved = 'v' . self::baseVersion();
    }

    public static function full(): string
    {
        if (self::$fullResolved !== null) {
            return self::$fullResolved;
        }

        $baseVersion = self::baseVersion();
        $gitHash = self::gitShortHash();

        if ($gitHash === null) {
            return self::$fullResolved = $baseVersion;
        }

        $version = $baseVersion . '+' . $gitHash;

        if (self::isDirty()) {
            $version .= '-dirty';
        }

        return self::$fullResolved = $version;
    }

    public static function baseVersion(): string
    {
        $version = trim((string) config('app.version', '1.1.0'));

        return $version !== '' ? $version : '1.1.0';
    }

    private static function gitShortHash(): ?string
    {
        $output = self::runGitCommand(['git', 'rev-parse', '--short=8', 'HEAD']);

        return $output !== null && $output !== '' ? $output : null;
    }

    private static function isDirty(): bool
    {
        $output = self::runGitCommand(['git', 'status', '--porcelain']);

        return $output !== null && trim($output) !== '';
    }

    private static function runGitCommand(array $command): ?string
    {
        try {
            $process = new Process($command, base_path());
            $process->setTimeout(2);
            $process->run();

            if (! $process->isSuccessful()) {
                return null;
            }

            return trim($process->getOutput());
        } catch (\Throwable) {
            return null;
        }
    }
}
