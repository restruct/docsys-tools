<?php

namespace DocSysTools;

use Composer\Autoload\ClassLoader;
use Composer\InstalledVersions;

class DocSysTools
{
    // @TODO: move/implement CLI calls to binaries in this class...(?)

    public static function check_wkhtml_patched($errorOnNonPatchedQT=true)
    {
        if(!defined('WKHTMLTOPDF_PATH')){
            self::init_paths();
        }

        $version = trim(shell_exec(WKHTMLTOPDF_PATH . ' -V'));
        $patched = strpos($version, 'with patched qt') !== false;
        if($errorOnNonPatchedQT && $patched===false) {
            user_error(WKHTMLTOPDF_PATH ?
                'Non-patched QT wkhtmltopdf, patched QT required for predictable PDFs (' . WKHTMLTOPDF_PATH . ')' :
                'No suitable wkhtmltopdf found'
            );
        }

        return $patched;
    }

    private static function error_if_required($isRequired, $namedConstant, $extraInfo='')
    {
        if($isRequired) {
            $tool = strtolower(str_replace('_PATH', '', $namedConstant));
            user_error("Please install/provide $tool $extraInfo", E_USER_ERROR);
        }
    }

    /**
     * Detect paths of installed binaries + 'export' as named constants
     *
     * @param string|array $reqSys require systems (DHUB/FUSE/FUSETOOLS) throw error in case a certain required tool cannot be found
     * @return void
     */
    public static function init_paths($reqSys = [])
    {
        if(is_string($reqSys)) {
            $reqSys = [ $reqSys ];
        }

        // arm64 (eg Mac M1) / x86_64 (most Linux)
        $ARCH = defined('DOCSYS_ARCH') ? DOCSYS_ARCH : trim(shell_exec('uname -m'));
        // Ubuntu / Debian / macOS
        if(defined('DOCSYS_OS_NAME')) {
            $OPSYS = DOCSYS_OS_NAME;
        } else {
            $OPSYS = trim(shell_exec('command -v lsb_release >/dev/null && lsb_release -is') ?: shell_exec('command -v sw_vers >/dev/null && sw_vers --productName'));
        }

        // FIRST option: detect OS-installed versions (if any)
        // Prefix /usr/local/bin + ~/bin to $PATH because it seemingly isn't present on some php shell_exec envs...
        $path_prefix = 'export PATH="/usr/local/bin:$HOME/bin:$PATH" && ';
        $paths = [
            ## DHUB + FUSE dependencies
            'WKHTMLTOPDF_PATH' => trim(shell_exec($path_prefix . 'which wkhtmltopdf')),
            'CPDF_PATH' => '', // always use module's cpdf
            ## FUSE dependencies
            'PDFINFO_PATH' => '', // always use module's pdfinfo
            'PDFTOPNG_PATH' => '', // always use module's pdftopng
            'CONVERT_PATH' => trim(shell_exec($path_prefix . 'which convert')),
            'GS_PATH' => trim(shell_exec($path_prefix . 'which gs')),
            'DOT_PATH' => realpath(InstalledVersions::getInstallPath('restruct/dot-static')).'/x64/dot_static',
            ## FUSETOOLS dependencies
            'SOFFICE_PATH' => trim(shell_exec($path_prefix . 'which soffice')),
        ];

        // Provide fallbacks for "xyz not found" paths
        $base_path = __DIR__ . DIRECTORY_SEPARATOR;
        $os_path = $OPSYS=='macOS' ? 'mac' : 'linux';
        $arch_path = $ARCH=='arm64' ? 'arm' : 'x64';
        $arch_path_xpdf = $OPSYS=='macOS' && $ARCH=='arm64' ? 'arm' : 'x64'; // XPDF-TOOLs Linux x64 supposedly works cross architecture(?)
        foreach ($paths as $key => $path) {
            if(file_exists($path)) {
                continue;
            }
            switch ($key) {
                case "WKHTMLTOPDF_PATH":
                    if($OPSYS=='macOS') {
                        $paths[$key] = $base_path . 'bin/wkhtmltox-0.12.6-osx/wkhtmltopdf';
                        break;
                    }
                    if($OPSYS=='Ubuntu') {
                        if(defined('DOCSYS_OS_VERSION')) {
                            $UbuntuVersion = (int) DOCSYS_OS_VERSION;
                        } else {
                            $UbuntuVersion = (int) shell_exec('command -v lsb_release >/dev/null && lsb_release -rs') ?: (int) shell_exec('command -v sw_vers >/dev/null && sw_vers --productVersion');
                        }
                        if($UbuntuVersion==16 || $UbuntuVersion==20) { //
                            $paths[$key] = $base_path . "bin/wkhtmltox-0.12.6-amd64-Ubuntu{$UbuntuVersion}.04/wkhtmltopdf"; // Ubuntu16.04/LIVE (v36156.2is.nl)
                        }
                        break;
                    }
                    self::error_if_required(in_array('DHUB', $reqSys) || in_array('FUSE', $reqSys), $key);
                    break;
                case "CPDF_PATH":
                    $paths[$key] = $base_path . "bin/cpdf-v2.8.1-{$os_path}-{$arch_path}/cpdf";
                    break;
                case "PDFINFO_PATH":
                    $paths[$key] = $base_path . "bin/xpdf-tools-4.05-{$os_path}-{$arch_path_xpdf}/pdfinfo";
                    break;
                case "PDFTOPNG_PATH":
                    $paths[$key] = $base_path . "bin/xpdf-tools-4.05-{$os_path}-{$arch_path_xpdf}/pdftopng";
                    break;
                case "DOT_PATH":
                    self::error_if_required(in_array('FUSE', $reqSys), $key, '(sudo apt install graphviz / brew install graphviz)');
                    break;
                case "CONVERT_PATH":
                    self::error_if_required(in_array('FUSE', $reqSys), $key, '(ImageMagick)');
                    break;
                case "GS_PATH":
                    self::error_if_required(in_array('FUSE', $reqSys), $key, '(GhostScript)');
                    break;
                case "SOFFICE_PATH":
                    self::error_if_required(in_array('FUSETOOLS', $reqSys), $key, '(CLI LibreOffice)');
                    break;
            }
        }

        ### Define/export as GLOBALS
        if(!defined('WKHTMLTOPDF_PATH')) define('WKHTMLTOPDF_PATH', $paths['WKHTMLTOPDF_PATH']);
        if(!defined('CPDF_PATH')) define('CPDF_PATH', $paths['CPDF_PATH']);
        if(!defined('PDFINFO_PATH')) define('PDFINFO_PATH', $paths['PDFINFO_PATH']);
        if(!defined('PDFTOPNG_PATH')) define('PDFTOPNG_PATH', $paths['PDFTOPNG_PATH']);

        if(!defined('DOT_PATH')) define('DOT_PATH', $paths['DOT_PATH']);
        if(!defined('CONVERT_PATH')) define('CONVERT_PATH', $paths['CONVERT_PATH']);
        if(!defined('GS_PATH')) define('GS_PATH', $paths['GS_PATH']);

        if(!defined('SOFFICE_PATH')) define('SOFFICE_PATH', $paths['SOFFICE_PATH']); // (actually hard-coded in FUSETOOLS for now but put here for reference)
    }

}