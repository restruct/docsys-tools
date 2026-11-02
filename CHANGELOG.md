# Changelog

## 2.0.0 (2026-09-29)

The umbrella layout that 1.0.1 to 1.0.3 shipped by accident, now released as the major version it
is. No code changes compared to 1.0.3.

**Breaking, compared to 1.0.0 and 1.0.4:**

- No `DocSysTools\DocSysTools` class: `init_paths()` and `check_wkhtml_patched()` are gone, and with
  them the `WKHTMLTOPDF_PATH`, `CPDF_PATH`, `PDFINFO_PATH`, `PDFTOPNG_PATH` and `DOT_PATH` constants
  they defined.
- No bundled binaries, no `bin/`, no `_config.php`.
- `restruct/cpdf-static` and `restruct/wkhtmltopdf-static` are suggested, not required. Require the
  ones you use directly.
- `bootstrap.php` (defines `GS_PATH`, `CONVERT_PATH`, `SOFFICE_PATH`) is not autoloaded; include it
  yourself if you use those constants.

See [UPGRADING.md](UPGRADING.md) for the steps and a before/after example.

## 1.0.4

Released from the `v1` branch (maintenance only): restores the 1.0.0 `DocSysTools` API on top of
the sub-packages, for consumers on `^1.0`. See the `v1` branch CHANGELOG.

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
