# Changelog

## 1.0.4 (2026-09-29)

**1.0.1, 1.0.2 and 1.0.3 were accidentally breaking releases.** They removed the
`DocSysTools\DocSysTools` class, `_config.php` and the bundled cpdf/wkhtmltopdf binaries, so any
consumer calling `DocSysTools::init_paths()` fatals with `Class "DocSysTools\DocSysTools" not found`
after a `composer update` within `^1.0`. That change belongs in a major release and is now 2.0.0.

1.0.4 restores 1.0.0 compatibility ([#3](https://github.com/restruct/docsys-tools/issues/3)):

- Restored `DocSysTools\DocSysTools` with the 1.0.0 public API: `init_paths($reqSys = [])` and
  `check_wkhtml_patched($errorOnNonPatchedQT = true)`, defining the same constants
  (`WKHTMLTOPDF_PATH`, `CPDF_PATH`, `PDFINFO_PATH`, `PDFTOPNG_PATH`, `DOT_PATH`, `CONVERT_PATH`,
  `GS_PATH`, `SOFFICE_PATH`). The class is deprecated and removed in 2.0.
- Binaries now come from the sub-packages instead of a bundled `bin/`:
  `restruct/wkhtmltopdf-static` (required again), `restruct/xpdf-static` and `restruct/dot-static`
  (already required), and `restruct/cpdf-static` if installed.
- `restruct/cpdf-static` stays suggested: it is only available from a private repository, and
  requiring it would make Composer silently fall back to 1.0.3 for anyone without access. Without it,
  `CPDF_PATH` falls back to an OS-installed `cpdf`, and is left undefined if there is none (1.0.0
  always defined it from its bundled binary).
- Restored the empty `_config.php`, so the package is again registered as a Silverstripe module, as
  in 1.0.0. It has no effect outside Silverstripe.
- `bin/` is not restored: its paths were never documented API, the constants were.

To keep using 1.x: nothing to change. To move to 2.x: see `UPGRADING.md` on the 2.x line.

## 1.0.3 (2026-09-25)

- README and `composer.json` metadata only. Still missing the 1.0.0 API, see 1.0.4.

## 1.0.2 (2026-07-02)

- Suggest text update. Still missing the 1.0.0 API, see 1.0.4.

## 1.0.1 (2026-07-02)

- Converted to an umbrella package; removed `DocSysTools\DocSysTools`, `_config.php`, `bin/` and the
  bundled binaries, and moved `restruct/cpdf-static` and `restruct/wkhtmltopdf-static` to "suggest".
  This was a breaking change released as a patch; see 1.0.4.

## 1.0.0 (2025-09-17)

- Prefer OS-installed binaries, bundled fallbacks (cpdf 2.8.1, xpdf-tools 4.05, wkhtmltopdf 0.12.6).
- `DocSysTools::init()` renamed to `init_paths()`.
