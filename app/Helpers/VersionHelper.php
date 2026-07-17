<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

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

        // Prefer the checked-out commit whenever Git metadata is available.
        // The stored file is only a deployment fallback and may be stale after
        // a pull, so it must never override the current repository version.
        $version = self::versionFromGitTag();

        if ($version !== null) {
            return self::$fullResolved = $version;
        }

        $version = self::versionFromStoredFile();

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

    public static function sourceVersion(): string
    {
        $version = self::versionFromGitTag();

        return $version ?? self::baseVersion();
    }

    public static function syncStoredVersion(): string
    {
        $version = self::sourceVersion();
        $path = self::storedVersionPath();

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $version . PHP_EOL);

        self::$fullResolved = $version;
        self::$displayResolved = 'v' . $version;

        return $version;
    }

    public static function resetCache(): void
    {
        self::$displayResolved = null;
        self::$fullResolved = null;
    }

    private static function versionFromStoredFile(): ?string
    {
        $path = self::storedVersionPath();

        if (! File::exists($path)) {
            return null;
        }

        $version = trim((string) File::get($path));

        return self::isValidVersion($version) ? $version : null;
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

    private static function storedVersionPath(): string
    {
        return storage_path('app/version.txt');
    }

    private static function isValidVersion(string $version): bool
    {
        return preg_match('/^\d+\.\d+\.\d+$/', $version) === 1;
    }

    private static function runGitCommand(array $command): ?string
    {
        try {
            $process = Process::path(base_path())
                ->timeout(2)
                ->run($command);

            if (! $process->successful()) {
                return null;
            }

            return trim($process->output());
        } catch (\Throwable) {
            return null;
        }
    }
}
