# Upgrading from 1.x to 2.0

2.0 is an umbrella package only. It no longer contains the `DocSysTools\DocSysTools` class, the
`*_PATH` constants it defined, or any binaries. Each binary now comes from its own sub-package, which
resolves its own path.

1.0.1 to 1.0.3 already had this layout by accident; 1.0.4 restored the 1.0.0 API on the 1.x line. If
you are not ready to change code, stay on `^1.0.4`.

## 1. Require the sub-packages you use directly

`restruct/xpdf-static` and `restruct/dot-static` are still required by this package, but require
directly whatever your own code calls, so you do not depend on the umbrella for it.

`restruct/wkhtmltopdf-static` is on Packagist:

```bash
composer require restruct/wkhtmltopdf-static:^1.0
```

`restruct/cpdf-static` is only available from a private repository (cpdf is AGPL or commercially
licensed). Add the repository entry, and make sure the deploy environment has a GitHub token with read
access to it:

```json
{
    "repositories": [
        { "type": "vcs", "url": "git@github.com:restruct/cpdf-static.git" }
    ],
    "require": {
        "restruct/cpdf-static": "^1.0"
    }
}
```

Then move docsys-tools itself to 2.x, or drop it if you only used it for these binaries:

```bash
composer require restruct/docsys-tools:^2.0
```

## 2. Replace `DocSysTools::init_paths()`

The sub-packages do not define anything on install. Each one includes its own `bootstrap.php` the
first time its class is used, and then defines its constant. So either ask the class for the path
(recommended), or call its `init()` if you still want the constant.

Before (1.x):

```php
use DocSysTools\DocSysTools;

public function init(): void
{
    parent::init();

    // Initialize CLI tools path constants
    DocSysTools::init_paths();

    if (defined('CPDF_PATH')) {
        self::config()->cpdf_path = CPDF_PATH;
    }
    if (defined('WKHTMLTOPDF_PATH')) {
        self::config()->wkhtmltopdf_path = WKHTMLTOPDF_PATH;
    }
}
```

After (2.x), asking each sub-package for its path:

```php
use Restruct\Cpdf\Cpdf;
use Restruct\WkhtmltoPdf\WkhtmltoPdfStatic;

public function init(): void
{
    parent::init();

    // Each getter returns '' when no binary could be resolved
    $cpdfPath = Cpdf::getPath();
    if ($cpdfPath !== '') {
        self::config()->cpdf_path = $cpdfPath;
    }
    $wkhtmltopdfPath = WkhtmltoPdfStatic::getPath();
    if ($wkhtmltopdfPath !== '') {
        self::config()->wkhtmltopdf_path = $wkhtmltopdfPath;
    }
}
```

Or, if code elsewhere reads the constants, initialise the sub-packages instead:

```php
\Restruct\Cpdf\Cpdf::init();                     // defines CPDF_PATH
\Restruct\WkhtmltoPdf\WkhtmltoPdfStatic::init(); // defines WKHTMLTOPDF_PATH (+ WKHTMLTOPDF_DOCKER_IMAGE on Linux)
```

## 3. Constant and method mapping

| 1.x | 2.x | Package |
|-----|-----|---------|
| `WKHTMLTOPDF_PATH` | `WkhtmltoPdfStatic::getPath()`, or `WKHTMLTOPDF_PATH` after `WkhtmltoPdfStatic::init()` | `restruct/wkhtmltopdf-static` |
| `CPDF_PATH` | `Cpdf::getPath()`, or `CPDF_PATH` after `Cpdf::init()` | `restruct/cpdf-static` |
| `PDFINFO_PATH` | `Xpdf::getToolPath('pdfinfo')` | `restruct/xpdf-static` |
| `PDFTOPNG_PATH` | `Xpdf::getToolPath('pdftopng')` | `restruct/xpdf-static` |
| `DOT_PATH` | `DotStatic::getPath()`, or `GRAPHVIZ_DOT_PATH` after `DotStatic::init()` | `restruct/dot-static` |
| `GS_PATH`, `CONVERT_PATH`, `SOFFICE_PATH` | same constants, after `require_once 'vendor/restruct/docsys-tools/bootstrap.php'` | `restruct/docsys-tools` |
| `DocSysTools::check_wkhtml_patched()` | `str_contains(WkhtmltoPdfStatic::version(), 'with patched qt')` | `restruct/wkhtmltopdf-static` |
| `init_paths($requiredSystems)` errors when a tool a listed system needs is missing | check `Cpdf::isAvailable()`, `WkhtmltoPdfStatic::isAvailable()`, `Xpdf::isAvailable()`, `DotStatic::isAvailable()` yourself | each package |
| `DOCSYS_ARCH`, `DOCSYS_OS_NAME`, `DOCSYS_OS_VERSION` | not needed: the sub-packages detect OS and architecture | - |

Class namespaces: `Restruct\Cpdf\Cpdf`, `Restruct\WkhtmltoPdf\WkhtmltoPdfStatic`,
`Restruct\Xpdf\Xpdf`, `Restruct\Dot\DotStatic`.

The sub-packages also offer command builders that replace hand-built shell calls, for example
`Restruct\Cpdf\CpdfCommand` and `Restruct\WkhtmltoPdf\WkhtmltoPdf` (a knp-snappy replacement). See
each package's README.

## 4. Behaviour differences to check

- **Resolution order.** 1.x preferred an OS-installed `wkhtmltopdf` found via `which`, then the
  bundled binary. `restruct/wkhtmltopdf-static` checks the `WKHTMLTOPDF_PATH` environment variable
  first, then on macOS uses its bundled binary, and on Linux `/usr/local/bin/wkhtmltopdf` before its
  bundled binary. `restruct/cpdf-static` never looks for an OS-installed cpdf. Every sub-package
  respects a constant you define before its first use, and its own environment variable.
- **Overrides by environment.** Each resolver reads an environment variable of the same name as its
  constant (`WKHTMLTOPDF_PATH`, `CPDF_PATH`, `XPDF_BIN_DIR`, `GRAPHVIZ_DOT_PATH`, `GS_PATH`,
  `CONVERT_PATH`, `SOFFICE_PATH`).
- **Docker.** On Linux, `restruct/wkhtmltopdf-static` can run wkhtmltopdf in a container
  (`WKHTMLTOPDF_DOCKER_IMAGE`) when no native binary works; `WkhtmltoPdfStatic::run()` picks the
  strategy. A bare `WKHTMLTOPDF_PATH` does not.
