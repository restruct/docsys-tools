# restruct/docsys-tools

Umbrella package that bundles all DocSys CLI tool wrappers and defines system tool paths.

## Sub-packages (bundled tools)

| Package | Constant | Tool | Dependency |
|---------|----------|------|------------|
| `restruct/xpdf-static` | `XPDF_BIN_DIR` | xpdf tools (pdftotext, pdfinfo, pdftopng, etc.) | required (Packagist) |
| `restruct/dot-static` | `GRAPHVIZ_DOT_PATH` | Graphviz dot (static Linux binary + Homebrew) | required (Packagist) |
| `restruct/cpdf-static` | `CPDF_PATH` | Coherent PDF v2.8.1 (macOS + Linux, x64 + ARM) | **suggested** — AGPL/commercial license, private repo only |
| `restruct/wkhtmltopdf-static` | `WKHTMLTOPDF_PATH` | wkhtmltopdf 0.12.6 with patched Qt + Docker | **suggested** — public GitHub repo (vcs), not on Packagist |

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

Each sub-package auto-initializes via its own bootstrap. To also define system tool paths:

```php
require_once 'vendor/restruct/docsys-tools/bootstrap.php';
```

Or override via environment variables:

```env
GS_PATH=/usr/bin/gs
CONVERT_PATH=/usr/bin/convert
SOFFICE_PATH=/usr/bin/soffice
```

## Migration from v0.x

The monolithic `DocSysTools::init_paths()` / `DocSysTools::init()` API is removed.
Each sub-package now handles its own binary resolution independently.

Constant changes:
- `XPDF_BIN_PATH` → `XPDF_BIN_DIR` (from restruct/xpdf-static)
- `PDFINFO_PATH` → use `Restruct\Xpdf\Xpdf::getToolPath('pdfinfo')`
- `PDFTOPNG_PATH` → use `Restruct\Xpdf\Xpdf::getToolPath('pdftopng')`
- `GRAPHVIZ_DOT_PATH` → still `GRAPHVIZ_DOT_PATH` (from restruct/dot-static)
- `DOT_PATH` → removed, use `GRAPHVIZ_DOT_PATH`

## System tool installation

See [previous README](https://github.com/restruct/docsys-tools/blob/f0db0eb/README.md) for detailed installation instructions for wkhtmltopdf, convert, gs, and soffice.
