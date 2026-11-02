<?php

namespace Restruct\DocSysTools\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The resolution order bootstrap.php documents, for each constant it defines:
 * predefined constant, then env var, then the OS's system paths.
 */
final class BootstrapTest extends TestCase
{
    /** @var list<string> */
    private array $stubFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->stubFiles as $file) {
            @unlink($file);
        }
        $this->stubFiles = [];
    }

    /**
     * A throwaway file standing in for a tool binary.
     */
    private function stub(bool $executable = true): string
    {
        $path = tempnam(sys_get_temp_dir(), 'docsysstub');
        file_put_contents($path, "#!/bin/sh\nexit 0\n");
        chmod($path, $executable ? 0755 : 0644);
        $this->stubFiles[] = $path;

        return $path;
    }

    /**
     * @return array<string, array{string}>
     */
    public static function constants(): array
    {
        $cases = [];
        foreach (array_keys(ChildPhp::TOOLS) as $constant) {
            $cases[$constant] = [$constant];
        }

        return $cases;
    }

    #[DataProvider('constants')]
    public function testPredefinedConstantWinsOverEnvVar(string $constant): void
    {
        $resolved = ChildPhp::constants(
            "define('$constant', '/predefined/tool');",
            [$constant => $this->stub()]
        );

        $this->assertSame('/predefined/tool', $resolved[$constant]);
    }

    #[DataProvider('constants')]
    public function testExecutableEnvVarWinsOverSystemPath(string $constant): void
    {
        $stub = $this->stub();

        $this->assertSame($stub, ChildPhp::constants('', [$constant => $stub])[$constant]);
    }

    #[DataProvider('constants')]
    public function testNonExecutableEnvVarFallsBackToSystemPath(string $constant): void
    {
        $resolved = ChildPhp::constants('', [$constant => $this->stub(false)]);

        $this->assertSame(ChildPhp::systemDefault($constant), $resolved[$constant]);
    }

    #[DataProvider('constants')]
    public function testEmptyEnvVarFallsBackToSystemPath(string $constant): void
    {
        $resolved = ChildPhp::constants('', [$constant => '']);

        $this->assertSame(ChildPhp::systemDefault($constant), $resolved[$constant]);
    }

    public function testWithNothingSetEachConstantIsItsSystemPathOrUndefined(): void
    {
        $expected = [];
        foreach (array_keys(ChildPhp::TOOLS) as $constant) {
            $expected[$constant] = ChildPhp::systemDefault($constant);
        }

        $this->assertSame($expected, ChildPhp::constants());
    }

    public function testConstantsResolveIndependently(): void
    {
        # A predefined GS_PATH must not short-circuit the other two
        $convert = $this->stub();
        $soffice = $this->stub();
        $resolved = ChildPhp::constants("define('GS_PATH', '/predefined/gs');", [
            'CONVERT_PATH' => $convert,
            'SOFFICE_PATH' => $soffice,
        ]);

        $this->assertSame([
            'GS_PATH' => '/predefined/gs',
            'CONVERT_PATH' => $convert,
            'SOFFICE_PATH' => $soffice,
        ], $resolved);
    }

    public function testIncludingTwiceKeepsTheFirstResolution(): void
    {
        # A second include must neither redefine (that would print a warning into the output)
        # nor pick up a changed env var
        $stub = $this->stub();
        $out = ChildPhp::run(
            'require ' . var_export(dirname(__DIR__) . '/bootstrap.php', true) . ';'
            . "putenv('GS_PATH=/elsewhere');"
            . ChildPhp::printConstants(),
            ['GS_PATH' => $stub]
        );

        $this->assertSame($stub, json_decode($out, true, flags: JSON_THROW_ON_ERROR)['GS_PATH']);
    }
}
