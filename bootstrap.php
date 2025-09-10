<?php

# NOTE: To remove the quarantine attribute from executable files on OSX, run: xattr -d com.apple.quarantine /path/to/file
use Composer\Autoload\ClassLoader;
use Composer\InstalledVersions;

$baseDir = __DIR__ . DIRECTORY_SEPARATOR;

# Detect environment and OS version if not defined
if(!defined('DOCSYS_OS_NAME')) {
    define('DOCSYS_OS_NAME', trim(shell_exec('command -v lsb_release >/dev/null && lsb_release -is') ?: shell_exec('command -v sw_vers >/dev/null && sw_vers --productName')));
}
if(!defined('DOCSYS_OS_VERSION') && DOCSYS_OS_NAME != 'macOS') { // distinquish Ubuntu 16.04 or 20/up
    define('DOCSYS_OS_VERSION', (int) shell_exec('command -v lsb_release >/dev/null && lsb_release -rs') ?: (int) shell_exec('command -v sw_vers >/dev/null && sw_vers --productVersion'));
}

if(DOCSYS_OS_NAME == 'macOS') { # OSX versions (local-DEV)

    ## SITE + FUSE dependencies
    define('WKHTMLTOPDF_PATH', $baseDir . 'bin/wkhtmltox-0.12.6-osx/wkhtmltopdf');
    define('CPDF_PATH', $baseDir . 'bin/cpdf-20201009-OSX-Intel/cpdf'); # license: 2.2-2017, latest = test version (using Intel on ARM with bridge)
    define('PDFINFO_PATH', $baseDir . 'bin/xpdfbin-4.02-mac-bin64/pdfinfo');
    define('XPDF_BIN_PATH', $baseDir . 'bin/xpdfbin-4.02-mac-bin64/');

    ## FUSE-only dependencies
    define('GRAPHVIZ_DOT_PATH', '/opt/homebrew/bin/dot');
    define('CONVERT_PATH', '/opt/homebrew/bin/convert');
    define('GS_PATH', '/opt/homebrew/bin/gs');

    ## FUSETOOLS-only dependencies
    define('SOFFICE_PATH', '/opt/homebrew/bin/soffice'); // (actually hard-coded in FUSETOOLS for now but put here for reference)

} else { # Linux/Ubuntu versions (TEST/LIVE)

    // makes it work on ddev (Debian 12) with package installed https://robfauls.com/install-wkhtmltopdf-on-debian12/
    if (trim(DOCSYS_OS_NAME) == 'Debian' && trim(DOCSYS_OS_VERSION) == '12') {
        define('WKHTMLTOPDF_PATH', '/usr/local/bin/wkhtmltopdf');
    } else {
        ## SITE + FUSE dependencies
        //define('WKHTMLTOPDF_PATH', $baseDir.DIRECTORY_SEPARATOR.'/vendor/h4cc/wkhtmltopdf-amd64/bin/wkhtmltopdf-amd64'); // 0.12.4 Ubuntu16.04/LIVE (v36156.2is.nl)
        if(DOCSYS_OS_VERSION == 16) {
            define('WKHTMLTOPDF_PATH', $baseDir . 'bin/wkhtmltox-0.12.6-amd64-Ubuntu16.04/wkhtmltopdf'); // 0.12.6 Ubuntu16.04/LIVE (v36156.2is.nl)
        } else {
            define('WKHTMLTOPDF_PATH', $baseDir . 'bin/wkhtmltox-0.12.6-amd64-Ubuntu20.04/wkhtmltopdf'); // 0.12.6 Ubuntu20.04/TEST
        }
    }

    define('CPDF_PATH', $baseDir . 'bin/cpdf-20201012-Linux-Intel-64bit/cpdf'); # Ubuntu16.04/LIVE (v36156.2is.nl)
    define('PDFINFO_PATH', $baseDir . 'bin/xpdfbin-4.02-linux-bin64/pdfinfo'); # Ubuntu16.04/LIVE (v36156.2is.nl)
    define('XPDF_BIN_PATH', $baseDir . 'bin/xpdfbin-4.02-linux-bin64/'); # Ubuntu16.04/LIVE (v36156.2is.nl)

    ## FUSE-only dependencies
    define('GRAPHVIZ_DOT_PATH', realpath(InstalledVersions::getInstallPath('restruct/dot-static')).'/x64/dot_static');
    define('CONVERT_PATH', '/usr/bin/convert'); // Ubuntu16.04/LIVE (v36156.2is.nl)
    define('GS_PATH', '/usr/bin/gs'); // Ubuntu16.04/LIVE (v36156.2is.nl)

    ## FUSETOOLS-only dependencies
    define('SOFFICE_PATH', '/usr/bin/soffice'); // (actually hard-coded in FUSETOOLS for now but put here for reference)
}
