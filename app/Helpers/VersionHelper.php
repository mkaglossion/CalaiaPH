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

        return self::$displayResolved = 'v' . self::full();
    }

    public static function full(): string
    {
        if (self::$fullResolved !== null) {
            return self::$fullResolved;
        }

        $version = self::versionFromGitTag();

        if ($version !== null) {
            return self::$fullResolved = $version;
        }

        return self::$fullResolved = self::baseVersion();
    }

    public static function baseVersion(): string
    {
        $version = trim((string) config('app.version', '1.1.0'));

        return $version !== '' ? $version : '1.1.0';
    }

    private static function versionFromGitTag(): ?string
    {
        $output = self::runGitCommand(['git', 'describe', '--tags', '--long', '--match', 'v[0-9]*']);

        if ($output === null || $output === '') {
            return null;
        }

        if (! preg_match('/^v(\d+)\.(\d+)\.(\d+)-(\d+)-g[0-9a-f]+$/i', $output, $matches)) {
            return null;
        }

        $major = (int) $matches[1];
        $minor = (int) $matches[2];
        $patch = (int) $matches[3] + (int) $matches[4];

        return $major . '.' . $minor . '.' . $patch;
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
