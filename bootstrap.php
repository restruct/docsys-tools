<?php

/**
 * Bootstrap for restruct/docsys-tools (umbrella package)
 *
 * Defines system tool paths for tools that are NOT bundled but expected
 * to be installed on the system. Sub-packages handle their own bootstrapping:
 * - restruct/cpdf-static → CPDF_PATH
 * - restruct/wkhtmltopdf-static → WKHTMLTOPDF_PATH, WKHTMLTOPDF_DOCKER_IMAGE
 * - restruct/xpdf-static → XPDF_BIN_DIR
 * - restruct/dot-static → GRAPHVIZ_DOT_PATH
 *
 * This bootstrap defines:
 * - GS_PATH (Ghostscript)
 * - CONVERT_PATH (ImageMagick/GraphicsMagick)
 * - SOFFICE_PATH (LibreOffice headless)
 *
 * Priority for each:
 * 1. Already defined constant
 * 2. Environment variable
 * 3. macOS: Homebrew paths (ARM first, then Intel)
 * 4. Linux: standard system paths
 */

# --- GS_PATH (Ghostscript) ---
if (!defined('GS_PATH')) {
    $gsPath = null;

    $envPath = getenv('GS_PATH');
    if ($envPath !== false && $envPath !== '' && is_executable($envPath)) {
        $gsPath = $envPath;
    }

    if ($gsPath === null) {
        $candidates = match (PHP_OS_FAMILY) {
            'Darwin' => ['/opt/homebrew/bin/gs', '/usr/local/bin/gs'],
            'Linux'  => ['/usr/bin/gs', '/usr/local/bin/gs'],
            default  => [],
        };
        foreach ($candidates as $candidate) {
            if (is_executable($candidate)) {
                $gsPath = $candidate;
                break;
            }
        }
    }

    if ($gsPath !== null) {
        define('GS_PATH', $gsPath);
    }
}

# --- CONVERT_PATH (ImageMagick/GraphicsMagick) ---
if (!defined('CONVERT_PATH')) {
    $convertPath = null;

    $envPath = getenv('CONVERT_PATH');
    if ($envPath !== false && $envPath !== '' && is_executable($envPath)) {
        $convertPath = $envPath;
    }

    if ($convertPath === null) {
        $candidates = match (PHP_OS_FAMILY) {
            'Darwin' => ['/opt/homebrew/bin/convert', '/usr/local/bin/convert'],
            'Linux'  => ['/usr/bin/convert', '/usr/local/bin/convert'],
            default  => [],
        };
        foreach ($candidates as $candidate) {
            if (is_executable($candidate)) {
                $convertPath = $candidate;
                break;
            }
        }
    }

    if ($convertPath !== null) {
        define('CONVERT_PATH', $convertPath);
    }
}

# --- SOFFICE_PATH (LibreOffice headless) ---
if (!defined('SOFFICE_PATH')) {
    $sofficePath = null;

    $envPath = getenv('SOFFICE_PATH');
    if ($envPath !== false && $envPath !== '' && is_executable($envPath)) {
        $sofficePath = $envPath;
    }

    if ($sofficePath === null) {
        $candidates = match (PHP_OS_FAMILY) {
            'Darwin' => ['/opt/homebrew/bin/soffice', '/usr/local/bin/soffice'],
            'Linux'  => ['/usr/bin/soffice', '/usr/local/bin/soffice'],
            default  => [],
        };
        foreach ($candidates as $candidate) {
            if (is_executable($candidate)) {
                $sofficePath = $candidate;
                break;
            }
        }
    }

    if ($sofficePath !== null) {
        define('SOFFICE_PATH', $sofficePath);
    }
}
