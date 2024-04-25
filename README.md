# Portable set of FUSE/DocSys CLI tools

To define named contants for the paths of various FUSE cli tools:  
`DocSysTools\DocSysTools::init();` in specific classes  
OR include in global scope (eg in project _config.php – less optimal, results in two shell_exec commands on every request):  
`require BASE_PATH . '/vendor/restruct/docsys-tools/bootstrap.php' );` eg in your _config.php 

To prevent running two shell_exec commands (to detect OS + version) altogether, define OS + version before instantiating DocSysTools;  
`define('DOCSYS_OS_NAME', 'macOS');`  
`define('DOCSYS_OS_VERSION', 13);`  
`DocSysTools\DocSysTools::init();`  

**NOTE (OSX):** apply +x/755 to (wkhtmltopdf) binaries on server:  
`chmod +x /path/to/file`

**NOTE (OSX):** "“wkhtmltopdf” cannot be opened because the developer cannot be verified."  
To remove the quarantine attribute from executable files on OSX: `xattr -d com.apple.quarantine /path/to/file`

**Wkhtmltopdf static builds**  
Last version is 0.12.4, after that they stopped because of library version issues between systems.  
- 0.12.4 can still just be downloaded and works (up untill Ubuntu 16.04, statically linked generic version); https://github.com/wkhtmltopdf/wkhtmltopdf/releases/0.12.4/
- 0.12.6 amd64 .deb for Ubuntu 16.04 and 20.04 works (download and extract from .deb, dynamically linked); https://wkhtmltopdf.org/downloads.html

**NOTE: .deb extraction**  
(https://www.cyberciti.biz/faq/how-to-extract-a-deb-file-without-opening-it-on-debian-or-ubuntu-linux/)  
unzipped & unzipped data.tar.xz, copied usr/local dir to docsys-tools/wkhtmltopdf-amd64-0.12.6-UbuntuXX.YY

