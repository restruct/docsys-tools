# DocSys tools: the PDF and document CLI tools in one require

One `composer require` for a PHP document pipeline's command-line tools: it pulls in the bundled
[xpdf tools](https://github.com/restruct/xpdf-static) and
[Graphviz dot](https://github.com/restruct/dot-static), points you at the optional
[wkhtmltopdf](https://github.com/restruct/wkhtmltopdf-static) and cpdf packages, and finds
Ghostscript, ImageMagick/GraphicsMagick and LibreOffice on the system for you.

*Maintained by [Restruct](https://github.com/restruct). If this package saves you time, you can
[support ongoing maintenance](https://github.com/sponsors/restruct).*

## A document pipeline needs half a dozen binaries

Generating, converting and inspecting documents from PHP means shelling out to a set of tools:
an HTML-to-PDF renderer, PDF text and metadata extraction, PDF merging and stamping, Ghostscript,
an image converter, LibreOffice for office formats, and Graphviz for diagrams. Each one lives at a
different path on macOS (Homebrew, ARM or Intel) and Linux, some are not installable on every
server, and every project ends up with its own copy of the path detection.

This umbrella package groups them: the tools that can be bundled come as sub-packages with their
own binaries and PHP wrappers, and the ones that must be installed on the system get their paths
detected by this package's `bootstrap.php`.

## Bundled tools, and system tool paths

### Sub-packages (bundled tools)

| Package | Constant | Tool | Dependency |
|---------|----------|------|------------|
| `restruct/xpdf-static` | `XPDF_BIN_DIR` | xpdf tools (pdftotext, pdfinfo, pdftopng, etc.) | required (Packagist) |
| `restruct/dot-static` | `GRAPHVIZ_DOT_PATH` | Graphviz dot (static Linux binary + Homebrew) | required (Packagist) |
| `restruct/cpdf-static` | `CPDF_PATH` | Coherent PDF v2.8.1 (macOS + Linux, x64 + ARM) | **suggested**: AGPL/commercial license, private repo only |
| `restruct/wkhtmltopdf-static` | `WKHTMLTOPDF_PATH` | wkhtmltopdf 0.12.6 with patched Qt + Docker | **suggested**: on Packagist (kept optional: not every consumer needs it) |

Each sub-package has its own bootstrap and PHP wrapper classes. The suggested packages must be
required directly by the consuming project (with the appropriate repository entry): this umbrella
only hard-requires what anyone can resolve from Packagist.

### System tool paths (this package)

This package's `bootstrap.php` defines paths for tools that must be installed on the system:

| Constant | Tool | Install |
|----------|------|---------|
| `GS_PATH` | Ghostscript | `apt install ghostscript` |
| `CONVERT_PATH` | ImageMagick/GraphicsMagick | `apt install graphicsmagick-imagemagick-compat` |
| `SOFFICE_PATH` | LibreOffice headless | `apt install libreoffice-nogui` |

For each constant it takes the first executable of:

1. the constant itself, if already defined
2. the environment variable of the same name
3. macOS: `/opt/homebrew/bin/<tool>` (Apple Silicon), then `/usr/local/bin/<tool>` (Intel)
4. Linux: `/usr/bin/<tool>`, then `/usr/local/bin/<tool>`

where `<tool>` is `gs`, `convert` or `soffice`. A tool that is not found leaves its constant
undefined, so check with `defined('GS_PATH')` before using it.

### Usage

Each sub-package resolves its binary on first use of its PHP class (for example
`Restruct\WkhtmltoPdf\WkhtmltoPdfStatic::getPath()` or `::init()`), which includes its own
bootstrap; nothing is defined on install. This package's `bootstrap.php` is not autoloaded either.
To also define the system tool paths:

```php
require_once 'vendor/restruct/docsys-tools/bootstrap.php';
```

Or override via environment variables:

```env
GS_PATH=/usr/bin/gs
CONVERT_PATH=/usr/bin/convert
SOFFICE_PATH=/usr/bin/soffice
```

## Technical

### Versions

| Version | Branch | Status | What it is |
|---------|--------|--------|------------|
| 2.x | `main` | current | Umbrella only. No `DocSysTools` class: use each sub-package's own resolver |
| 1.x | `v1` | compatibility, maintenance only | 1.0.4+ keeps the 1.0.0 `DocSysTools::init_paths()` API (deprecated), delegating to the sub-packages |

1.0.1 to 1.0.3 removed the 1.0.0 API in patch releases by accident; 1.0.4 restores it. Moving from
1.x to 2.x: see [UPGRADING.md](UPGRADING.md). History: [CHANGELOG.md](CHANGELOG.md).

### Requirements

- PHP 8.2 or higher in practice. This package's own `bootstrap.php` needs PHP 8.0 (`composer.json`
  says `>=8.0`), but `restruct/dot-static` ^2.0 requires PHP 8.2 and `restruct/xpdf-static` ^1.0
  requires PHP 8.1.
- The system tools above (Ghostscript, ImageMagick/GraphicsMagick, LibreOffice) if you use their
  constants; they are not bundled.

`composer.json` is the source of truth for version constraints. This is a plain PHP package; it
does not depend on any framework.

### Installation

```bash
composer require restruct/docsys-tools
```

Then require the suggested sub-packages you use directly (see
[UPGRADING.md](UPGRADING.md#1-require-the-sub-packages-you-use-directly) for the cpdf repository
entry):

```bash
composer require restruct/wkhtmltopdf-static
```

### Migrating from 0.x or 1.x

The monolithic `DocSysTools::init_paths()` (1.x) / `DocSysTools::init()` (0.x) API is removed.
Each sub-package now handles its own binary resolution independently. Coming from 1.x: see
[UPGRADING.md](UPGRADING.md) for the full mapping and a before/after example.

Constant changes:
- `XPDF_BIN_PATH` (0.x) to `XPDF_BIN_DIR` (from restruct/xpdf-static)
- `PDFINFO_PATH` (1.x): use `Restruct\Xpdf\Xpdf::getToolPath('pdfinfo')`
- `PDFTOPNG_PATH` (1.x): use `Restruct\Xpdf\Xpdf::getToolPath('pdftopng')`
- `GRAPHVIZ_DOT_PATH` (0.x): still `GRAPHVIZ_DOT_PATH` (from restruct/dot-static)
- `DOT_PATH` (1.x): removed, use `GRAPHVIZ_DOT_PATH`

### System tool installation

See the [previous README](https://github.com/restruct/docsys-tools/blob/f0db0eb/README.md) for
detailed installation instructions for wkhtmltopdf, convert, gs, and soffice.

### License

This package (`bootstrap.php`, docs) is MIT licensed, see [LICENSE](LICENSE). The sub-packages it
requires or suggests carry their own licences, including those of the third-party binaries they
bundle: see each package's README (xpdf tools: GPL v2 or v3; Graphviz: Eclipse Public License;
wkhtmltopdf: LGPL-3.0; Coherent PDF: AGPL or commercial).
