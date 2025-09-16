# Portable set of FUSE/DocSys CLI tools

Helper module for various binaries required for FUSE/DocSys:
#### DHUB + FUSE dependencies
- wkhtmltopdf (with patched QT, 0.12.6 fallbacks for Ubuntu 16/20 & OSX included)
- cpdf (included)
#### FUSE dependencies
- pdfinfo (included)
- pdftopng (included)
- convert (Imagemagick)
- gs (Ghostscript)
- dot (Graphviz, installed as dependency)
#### FUSETOOLS dependencies
- soffice (CLI LibreOffice)

## Configuration
No configuration necessary, to detect & define contants for the paths of above cli tools:
```php
DocSysTools\DocSysTools::init_paths();
```

Set required systems (DHUB/FUSE/FUSETOOLS) to halt execution if a certain tool for a system cannot be found:
```php
DocSysTools\DocSysTools::init_paths(['DHUB', 'FUSE']);
```

To save running a few shell commands (to detect arch, OS + version), define these as constants:  
```php
define('DOCSYS_ARCH', 'arm64'); # arm64 / x86_64
define('DOCSYS_OS_NAME', 'macOS'); # Ubuntu / Debian / macOS

# Only for Ubuntu and if no system-installed wkhtmltopdf:
define('DOCSYS_OS_VERSION', 16); #  16 / 20
```

## Update constants from version 0.* to 1.*
- `DocSysTools\DocSysTools::init()` renamed to `init_paths()`
- removed/replaced `XPDF_BIN_PATH` with `PDFTOPNG_PATH`
- changed `GRAPHVIZ_DOT_PATH` to `DOT_PATH`

### wkhtmltopdf with PATCHED QT required for predictable PDFs
In case of unexpected scaling issues (and/or other unpredictable behaviour such as random missing images etc) in generated PDFs, make sure you're using a wkhtmltopdf with **patched QT**:  
```shell
wkhtmltopdf -V # should include "wkhtmltopdf [...] (with patched qt)"
```
Or in your php code (eg on dev/build task):
```php
DocSysTools::check_wkhtml_patched($errorOnNonPatchedQT=false); // true to throw an error on unpatched QT
```

### Recommended: install wkhtmltopdf 0.12.6 (WITH PATCHED QT!)

#### wkhtmltopdf on Ubuntu/Debian
```shell
# Remove 'normal' (non-patched QT version) if required
sudo apt-get remove wkhtmltopdf 
sudo apt autoremove

# Download latest release (jammy/22.04 release also works for noble/24.04)
wget https://github.com/wkhtmltopdf/packaging/releases/download/0.12.6.1-3/wkhtmltox_0.12.6.1-3.jammy_amd64.deb

# Install dependencies
sudo apt-get update
sudo apt-get install -y libfontconfig1 libfreetype6 libx11-6 libxext6 libxrender1 xfonts-75dpi xfonts-base

apt-get install libfontenc1 xfonts-75dpi xfonts-base xfonts-encodings xfonts-utils openssl build-essential libssl-dev libxrender-dev git-core libx11-dev libxext-dev libfontconfig1-dev libfreetype6-dev fontconfig -y

# Install wkhtmltopdf
sudo dpkg -i wkhtmltox_0.12.6.1-3.jammy_amd64.deb
sudo apt-get install -f

# Check that wkhtmltopdf with patched QT got installed
wkhtmltopdf -V # should return "wkhtmltopdf 0.12.6.1 (with patched qt)"

# In case dpkg throws errors you may need to run
sudo apt --fix-broken install

# On Debian 12, libjpeg-turbo8 may be missing;
wget http://mirrors.kernel.org/ubuntu/pool/main/libj/libjpeg-turbo/libjpeg-turbo8_2.1.2-0ubuntu1_amd64.deb
sudo apt install ./libjpeg-turbo8_2.1.2-0ubuntu1_amd64.deb
```

For other operating systems, see this [guide](https://chyshkala.com/blog/wkhtmltopdf-with-patched-qt-the-complete-developer-s-guide), in case of troubles you may find a solution [here](https://stackoverflow.com/questions/34479040/how-to-install-wkhtmltopdf-with-patched-qt) 


### (Some) wkhtmltopdf fallbacks included in this module
For environments where installing is not an option (eg shared hosting), fallbacks for Ubuntu 16 & 20 and OSX (Intel/rosetta) are included.
Static builds were discontinued after 0.12.4 because of library version issues between systems so the included 0.12.6 versions may work if all required libs happen to be available on your system.


## DEV NOTES:

### (Legacy) wkhtmltopdf static build (0.12.4)
0.12.4 can still just be downloaded and works (up untill Ubuntu 16.04, statically linked generic version); https://github.com/wkhtmltopdf/wkhtmltopdf/releases/0.12.4/

#### .deb extraction
  (https://www.cyberciti.biz/faq/how-to-extract-a-deb-file-without-opening-it-on-debian-or-ubuntu-linux/)  
  unzipped & unzipped data.tar.xz, copied usr/local dir to docsys-tools/wkhtmltopdf-amd64-0.12.6-UbuntuXX.YY

### Wkhtmltopdf (dynamically linked)
0.12.6 amd64 .deb for Ubuntu 16.04 and 20.04 works IF ALL requirements happen to be installed (download and extract from .deb, dynamically linked); https://wkhtmltopdf.org/downloads.html

### (OSX) make (wkhtmltopdf) binaries executable  
`chmod +x /path/to/file`

### (OSX) *“wkhtmltopdf” cannot be opened because the developer cannot be verified.*  
To remove the quarantine attribute from executable files on OSX: `xattr -d com.apple.quarantine /path/to/file`
