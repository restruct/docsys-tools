<?php

namespace Restruct\DocSysTools\Tests;

use PHPUnit\Framework\TestCase;

/**
 * The umbrella's required sub-packages, as installed through it, find their bundled Linux
 * binaries: the point of requiring this one package. Each check runs in a child process with
 * the sub-package's env var removed, so only the bundled binary can satisfy it.
 */
final class SubPackagesTest extends TestCase
{
    protected function setUp(): void
    {
        if (PHP_OS_FAMILY !== 'Linux') {
            $this->markTestSkipped('The bundled binaries are what resolves on Linux; elsewhere it depends on local installs');
        }
    }

    public function testDotStaticResolvesItsBundledBinary(): void
    {
        $out = ChildPhp::run(
            'echo json_encode([\Restruct\Dot\DotStatic::isAvailable(), \Restruct\Dot\DotStatic::getPath()]);',
            ['GRAPHVIZ_DOT_PATH' => false]
        );
        [$available, $path] = json_decode($out, true, flags: JSON_THROW_ON_ERROR);

        $this->assertTrue($available);
        $this->assertStringEndsWith('/vendor/restruct/dot-static/x64/dot_static', $path);
    }

    public function testXpdfStaticResolvesItsBundledBinaries(): void
    {
        $out = ChildPhp::run(
            'echo json_encode([\Restruct\Xpdf\Xpdf::isAvailable(), \Restruct\Xpdf\Xpdf::getBinDir()]);',
            ['XPDF_BIN_DIR' => false]
        );
        [$available, $dir] = json_decode($out, true, flags: JSON_THROW_ON_ERROR);

        $this->assertTrue($available);
        $this->assertStringEndsWith('/vendor/restruct/xpdf-static/x64/linux', $dir);
    }
}
