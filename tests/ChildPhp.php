<?php

namespace Restruct\DocSysTools\Tests;

use Symfony\Component\Process\Process;

/**
 * Runs a PHP snippet in a fresh PHP process.
 *
 * bootstrap.php defines each constant once per process and a constant cannot be undefined, so
 * every resolution case (predefined constant, env var, system path) needs its own process.
 */
final class ChildPhp
{
    /** The constants bootstrap.php defines, with the binary name it looks for on the system */
    public const TOOLS = [
        'GS_PATH' => 'gs',
        'CONVERT_PATH' => 'convert',
        'SOFFICE_PATH' => 'soffice',
    ];

    /**
     * @param string $code PHP code without the opening tag; its stdout is returned
     * @param array<string, string|false> $env Environment overrides; false removes a variable
     */
    public static function run(string $code, array $env = []): string
    {
        # Every tool variable is removed unless a case sets it, so a value in the developer's own
        # shell cannot leak into the expectation of a "no env var" case
        foreach (array_keys(self::TOOLS) as $constant) {
            $env += [$constant => false];
        }

        $prelude = 'require ' . var_export(dirname(__DIR__) . '/vendor/autoload.php', true) . ';';
        $process = new Process([PHP_BINARY, '-r', $prelude . $code], null, $env);
        $process->setTimeout(30);
        $process->mustRun();

        return $process->getOutput();
    }

    /**
     * PHP that includes bootstrap.php and prints every tool constant as JSON (null = undefined).
     */
    public static function printConstants(): string
    {
        return 'require ' . var_export(dirname(__DIR__) . '/bootstrap.php', true) . ';'
            . '$out = [];'
            . 'foreach (' . var_export(array_keys(self::TOOLS), true) . ' as $c) {'
            . '    $out[$c] = defined($c) ? constant($c) : null;'
            . '}'
            . 'echo json_encode($out);';
    }

    /**
     * @return array<string, ?string>
     */
    public static function constants(string $before = '', array $env = []): array
    {
        return json_decode(self::run($before . self::printConstants(), $env), true, flags: JSON_THROW_ON_ERROR);
    }

    /**
     * What bootstrap.php resolves a constant to with no constant and no env var: the first
     * executable system path for this OS, or null. Mirrors bootstrap.php's candidate lists.
     */
    public static function systemDefault(string $constant): ?string
    {
        $bin = self::TOOLS[$constant];
        $candidates = match (PHP_OS_FAMILY) {
            'Darwin' => ["/opt/homebrew/bin/$bin", "/usr/local/bin/$bin"],
            'Linux' => ["/usr/bin/$bin", "/usr/local/bin/$bin"],
            default => [],
        };
        foreach ($candidates as $candidate) {
            if (is_executable($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
