<?php

namespace DocSysTools;

use Restruct\Cpdf\Cpdf;
use Restruct\Dot\DotStatic;
use Restruct\WkhtmltoPdf\WkhtmltoPdfStatic;
use Restruct\Xpdf\Xpdf;

/**
 * 1.x compatibility shim: restores the 1.0.0 API (init_paths(), check_wkhtml_patched() and the
 * *_PATH constants) on top of the sub-packages that replaced the binaries bundled in 1.0.0.
 *
 * Binary resolution is delegated:
 * - WKHTMLTOPDF_PATH: OS-installed wkhtmltopdf first (as in 1.0.0), else restruct/wkhtmltopdf-static
 * - CPDF_PATH: restruct/cpdf-static if installed (private repository), else OS-installed cpdf
 * - PDFINFO_PATH, PDFTOPNG_PATH: restruct/xpdf-static
 * - DOT_PATH: restruct/dot-static
 * - CONVERT_PATH, GS_PATH, SOFFICE_PATH: OS-installed only, unchanged from 1.0.0
 *
 * @deprecated 1.0.4 Removed in restruct/docsys-tools 2.0. Require restruct/wkhtmltopdf-static,
 *             restruct/cpdf-static, restruct/xpdf-static and restruct/dot-static directly and use
 *             their own path resolvers, see UPGRADING.md on the 2.x branch:
 *             https://github.com/restruct/docsys-tools/blob/main/UPGRADING.md
 */
class DocSysTools
{
    // @TODO: move/implement CLI calls to binaries in this class...(?)

    /**
     * @deprecated 1.0.4 Use Restruct\WkhtmltoPdf\WkhtmltoPdfStatic::version() (restruct/wkhtmltopdf-static)
     */
    public static function check_wkhtml_patched($errorOnNonPatchedQT=true)
    {
        if(!defined('WKHTMLTOPDF_PATH')){
            self::init_paths();
        }

        # Guard against an empty path: 1.0.0 would run ' -V' as a command here
        $version = WKHTMLTOPDF_PATH ? trim((string) shell_exec(escapeshellarg(WKHTMLTOPDF_PATH) . ' -V')) : '';
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
     * DOCSYS_ARCH, DOCSYS_OS_NAME and DOCSYS_OS_VERSION are still accepted but no longer read: they only
     * selected a bundled binary from bin/, and the sub-packages detect OS and architecture themselves.
     *
     * @deprecated 1.0.4 Use the sub-packages' own resolvers, eg WkhtmltoPdfStatic::getPath(), Cpdf::getPath()
     * @param string|array $reqSys require systems (DHUB/FUSE/FUSETOOLS) throw error in case a certain required tool cannot be found
     * @return void
     */
    public static function init_paths($reqSys = [])
    {
        if(is_string($reqSys)) {
            $reqSys = [ $reqSys ];
        }

        // FIRST option: detect OS-installed versions (if any)
        // Prefix /usr/local/bin + ~/bin to $PATH because it seemingly isn't present on some php shell_exec envs...
        $path_prefix = 'export PATH="/usr/local/bin:$HOME/bin:$PATH" && ';
        # (string) cast: shell_exec() returns null when `which` finds nothing, and trim(null) is deprecated on PHP 8.1+
        $paths = [
            ## DHUB + FUSE dependencies
            'WKHTMLTOPDF_PATH' => trim((string) shell_exec($path_prefix . 'which wkhtmltopdf')),
            'CPDF_PATH' => '', // always use cpdf-static's cpdf if installed (1.0.0: module's bundled cpdf)
            ## FUSE dependencies
            'PDFINFO_PATH' => '', // always use xpdf-static's pdfinfo (1.0.0: module's bundled pdfinfo)
            'PDFTOPNG_PATH' => '', // always use xpdf-static's pdftopng (1.0.0: module's bundled pdftopng)
            'CONVERT_PATH' => trim((string) shell_exec($path_prefix . 'which convert')),
            'GS_PATH' => trim((string) shell_exec($path_prefix . 'which gs')),
            'DOT_PATH' => '', // always use dot-static's dot
            ## FUSETOOLS dependencies
            'SOFFICE_PATH' => trim((string) shell_exec($path_prefix . 'which soffice')),
        ];

        // Provide fallbacks for "xyz not found" paths, from the sub-packages (1.0.0: from this module's bin/)
        foreach ($paths as $key => $path) {
            if(file_exists($path)) {
                continue;
            }
            switch ($key) {
                case "WKHTMLTOPDF_PATH":
                    # Required package; its bootstrap respects an already defined WKHTMLTOPDF_PATH
                    if(class_exists(WkhtmltoPdfStatic::class)) {
                        $paths[$key] = WkhtmltoPdfStatic::getPath();
                    }
                    if(!$paths[$key]) {
                        self::error_if_required(in_array('DHUB', $reqSys) || in_array('FUSE', $reqSys), $key);
                    }
                    break;
                case "CPDF_PATH":
                    # restruct/cpdf-static is only available from a private repository (cpdf is AGPL/commercial),
                    # so it is suggested, not required. Without it, fall back to an OS-installed cpdf.
                    if(class_exists(Cpdf::class)) {
                        $paths[$key] = Cpdf::getPath();
                    }
                    if(!$paths[$key]) {
                        $paths[$key] = trim((string) shell_exec($path_prefix . 'which cpdf'));
                    }
                    if(!$paths[$key]) {
                        self::error_if_required(in_array('DHUB', $reqSys) || in_array('FUSE', $reqSys), $key, '(require restruct/cpdf-static, private repository)');
                    }
                    break;
                case "PDFINFO_PATH":
                    $paths[$key] = Xpdf::getToolPath('pdfinfo');
                    break;
                case "PDFTOPNG_PATH":
                    $paths[$key] = Xpdf::getToolPath('pdftopng');
                    break;
                case "DOT_PATH":
                    $paths[$key] = DotStatic::getPath();
                    if(!$paths[$key]) {
                        self::error_if_required(in_array('FUSE', $reqSys), $key, '(sudo apt install graphviz / brew install graphviz)');
                    }
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
        # CPDF_PATH is only defined when resolved (1.0.0 always had its bundled cpdf): an empty constant would
        # override a consumer's own default (eg 'cpdf' on PATH) and block cpdf-static's bootstrap later on
        if(!defined('CPDF_PATH') && $paths['CPDF_PATH']) define('CPDF_PATH', $paths['CPDF_PATH']);
        if(!defined('PDFINFO_PATH')) define('PDFINFO_PATH', $paths['PDFINFO_PATH']);
        if(!defined('PDFTOPNG_PATH')) define('PDFTOPNG_PATH', $paths['PDFTOPNG_PATH']);

        if(!defined('DOT_PATH')) define('DOT_PATH', $paths['DOT_PATH']);
        if(!defined('CONVERT_PATH')) define('CONVERT_PATH', $paths['CONVERT_PATH']);
        if(!defined('GS_PATH')) define('GS_PATH', $paths['GS_PATH']);

        if(!defined('SOFFICE_PATH')) define('SOFFICE_PATH', $paths['SOFFICE_PATH']); // (actually hard-coded in FUSETOOLS for now but put here for reference)
    }

}
