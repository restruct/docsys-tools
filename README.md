# restruct/docsys-tools

*Maintained by [Restruct](https://github.com/restruct). If this package saves you time, you can
[support ongoing maintenance](https://github.com/sponsors/restruct).*

Umbrella package that bundles all DocSys CLI tool wrappers and defines system tool paths.

> **This is the 1.x line (maintenance only).** 1.0.4 restores the 1.0.0 API
> (`DocSysTools\DocSysTools::init_paths()`, `check_wkhtml_patched()` and the `*_PATH` constants),
> which 1.0.1 to 1.0.3 removed by accident. The class is deprecated: for new code use the
> sub-packages directly, see the 2.x line and its `UPGRADING.md`. See [CHANGELOG.md](CHANGELOG.md).

| Version | Status | What it is |
|---------|--------|------------|
| 1.x | maintenance only | 1.0.0-compatible `DocSysTools` class, delegating to the sub-packages |
| 2.x | current | umbrella only: no `DocSysTools` class, use each sub-package's own resolver |

## Requirements

- PHP 8.2 or higher in practice. This package's own `bootstrap.php` needs PHP 8.0 (`composer.json`
  says `>=8.0`), but `restruct/dot-static` ^2.0 requires PHP 8.2 and `restruct/xpdf-static` ^1.0
  requires PHP 8.1.
- The system tools below (Ghostscript, ImageMagick/GraphicsMagick, LibreOffice) if you use their
  constants; they are not bundled.

`composer.json` is the source of truth for version constraints.

## Sub-packages (bundled tools)

| Package | Constant | Tool | Dependency |
|---------|----------|------|------------|
| `restruct/xpdf-static` | `XPDF_BIN_DIR` | xpdf tools (pdftotext, pdfinfo, pdftopng, etc.) | required (Packagist) |
| `restruct/dot-static` | `GRAPHVIZ_DOT_PATH` | Graphviz dot (static Linux binary + Homebrew) | required (Packagist) |
| `restruct/cpdf-static` | `CPDF_PATH` | Coherent PDF v2.8.1 (macOS + Linux, x64 + ARM) | **suggested** — AGPL/commercial license, private repo only |
| `restruct/wkhtmltopdf-static` | `WKHTMLTOPDF_PATH` | wkhtmltopdf 0.12.6 with patched Qt + Docker | required (Packagist), since 1.0.4 again: `DocSysTools::init_paths()` relies on it |

Each sub-package has its own bootstrap and PHP wrapper classes. The suggested
packages must be required directly by the consuming project (with the
appropriate repository entry) — this umbrella only hard-requires what anyone
can resolve from Packagist.

## System tool paths (this package)

This package's `bootstrap.php` defines paths for tools that must be installed on the system:

| Constant | Tool | Install |
|----------|------|---------|
| `GS_PATH` | Ghostscript | `apt install ghostscript` |
| `CONVERT_PATH` | ImageMagick/GraphicsMagick | `apt install graphicsmagick-imagemagick-compat` |
| `SOFFICE_PATH` | LibreOffice headless | `apt install libreoffice-nogui` |

## Usage

Each sub-package resolves its binary on first use of its PHP class (for example
`Restruct\WkhtmltoPdf\WkhtmltoPdfStatic::getPath()`), which includes its own bootstrap. To also define
system tool paths:

```php
require_once 'vendor/restruct/docsys-tools/bootstrap.php';
```

Or override via environment variables:

```env
GS_PATH=/usr/bin/gs
CONVERT_PATH=/usr/bin/convert
SOFFICE_PATH=/usr/bin/soffice
```

## 1.0.0 compatibility API (deprecated)

1.0.4 restores the 1.0.0 entry point. Existing code keeps working unchanged:

```php
use DocSysTools\DocSysTools;

DocSysTools::init_paths();                  // defines the *_PATH constants below
DocSysTools::init_paths(['DHUB', 'FUSE']);  // same, but errors if a tool for that system is missing
DocSysTools::check_wkhtml_patched(false);   // true if wkhtmltopdf reports "with patched qt"
```

| Constant | Resolved from (1.0.4) | 1.0.0 |
|----------|-----------------------|-------|
| `WKHTMLTOPDF_PATH` | OS-installed `wkhtmltopdf`, else `restruct/wkhtmltopdf-static` | OS-installed, else bundled `bin/` |
| `CPDF_PATH` | `restruct/cpdf-static` if installed, else OS-installed `cpdf`; not defined if neither | bundled `bin/` |
| `PDFINFO_PATH`, `PDFTOPNG_PATH` | `restruct/xpdf-static` | bundled `bin/` |
| `DOT_PATH` | `restruct/dot-static` | `restruct/dot-static` (Linux binary path) |
| `CONVERT_PATH`, `GS_PATH`, `SOFFICE_PATH` | OS-installed (`which`) | same |

`restruct/cpdf-static` is suggested, not required: it is only available from a private repository
(cpdf is AGPL or commercially licensed), and a hard requirement would make Composer silently install
1.0.3 instead for everyone without access to it. If you use cpdf, require it directly (with its VCS
repository entry) or install cpdf on the system.

`DOCSYS_ARCH`, `DOCSYS_OS_NAME` and `DOCSYS_OS_VERSION` are still accepted but no longer read; the
sub-packages detect OS and architecture themselves.

## Migration from v0.x

The `DocSysTools::init()` API from 0.x is removed (renamed to `init_paths()` in 1.0.0).
In 2.x `DocSysTools::init_paths()` is removed as well, and each sub-package handles its own binary
resolution independently.

Constant changes (2.x, when not using the 1.x `init_paths()`):
- `XPDF_BIN_PATH` → `XPDF_BIN_DIR` (from restruct/xpdf-static)
- `PDFINFO_PATH` → use `Restruct\Xpdf\Xpdf::getToolPath('pdfinfo')`
- `PDFTOPNG_PATH` → use `Restruct\Xpdf\Xpdf::getToolPath('pdftopng')`
- `GRAPHVIZ_DOT_PATH` → still `GRAPHVIZ_DOT_PATH` (from restruct/dot-static)
- `DOT_PATH` → removed, use `GRAPHVIZ_DOT_PATH`

## System tool installation

See [previous README](https://github.com/restruct/docsys-tools/blob/f0db0eb/README.md) for detailed installation instructions for wkhtmltopdf, convert, gs, and soffice.

## License

This package (`bootstrap.php`, docs) is MIT licensed, see [LICENSE](LICENSE). The sub-packages it
requires or suggests carry their own licences, including those of the third-party binaries they
bundle: see each package's README (xpdf tools: GPL v2 or v3; Graphviz: Eclipse Public License;
wkhtmltopdf: LGPL-3.0; Coherent PDF: AGPL or commercial).
