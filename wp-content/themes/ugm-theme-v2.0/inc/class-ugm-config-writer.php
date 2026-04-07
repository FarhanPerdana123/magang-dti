<?php

class UGM_Config_Writer {

	public function __construct() {
		add_action( 'after_setup_theme', [ $this, 'set_auto_update' ] );
		add_action( 'after_setup_theme', [ $this, 'set_htaccess' ] );
	}

	public function set_auto_update() {
		$file_path = $this->locate_file( 'wp-config.php' );
		if ( function_exists('file_get_contents') ) {
			$config_data = @file_get_contents( $file_path );
		}
		if ( $config_data === false ) {
			return;
		}
		$new_config_data = preg_replace(
			"~\\/\\*\\* Enable auto update \\*\\*?\\/.*?\\/\\/ Added by ugm theme(\r\n)*~s",
			'', $config_data );
		$new_config_data = preg_replace(
			"~(\\/\\/\\s*)?define\\s*\\(\\s*['\"]?WP_AUTO_UPDATE_CORE['\"]?\\s*,.*?\\)\\s*;+\\r?\\n?~is",
			'', $new_config_data );
		$new_config_data = preg_replace(
			'~<\?(php)?~',
			"\\0\r\n" . $this->wp_config_addition(),
			$new_config_data, 1 );

		if ( $new_config_data != $config_data ) {
			try {
				@file_put_contents( $file_path, $new_config_data );
			} catch ( Util_WpFile_FilesystemOperationException $ex ) {
				throw new Util_WpFile_FilesystemModifyException(
					$ex->getMessage(), $ex->credentials_form(),
					'Edit file <strong>' . $file_path .
					'</strong> and add next lines:', $file_path,
					$this->wp_config_addition() );
			}
		}
	}

	public function set_htaccess() {
		$file_path = $this->locate_file( '.htaccess' );
        if ( ! $file_path ) {
            return;
        }
		if ( function_exists('file_get_contents') ) {
			$config_data = @file_get_contents( $file_path );
		}
		if ( $config_data === false ) {
			return;
		}

		if ( false === strpos( $config_data, "### end ugm theme" ) ) {
			$new_config_data = $this->htaccess_addition();
			file_put_contents( $file_path, $new_config_data . $config_data );
		}

	}

	private function wp_config_addition() {
		return "/** Enable auto update */\r\n" .
				"define('WP_AUTO_UPDATE_CORE', 'minor'); // Added by ugm theme\r\n";
	}

	private function htaccess_addition() {
		return '### Start UGM Theme
<IfModule mod_mime.c>
    AddType text/css .css
    AddType text/x-component .htc
    AddType application/x-javascript .js
    AddType application/javascript .js2
    AddType text/javascript .js3
    AddType text/x-js .js4
    AddType video/asf .asf .asx .wax .wmv .wmx
    AddType video/avi .avi
    AddType image/bmp .bmp
    AddType application/java .class
    AddType video/divx .divx
    AddType application/msword .doc .docx
    AddType application/vnd.ms-fontobject .eot
    AddType application/x-msdownload .exe
    AddType image/gif .gif
    AddType application/x-gzip .gz .gzip
    AddType image/x-icon .ico
    AddType image/jpeg .jpg .jpeg .jpe
    AddType application/json .json
    AddType application/vnd.ms-access .mdb
    AddType audio/midi .mid .midi
    AddType video/quicktime .mov .qt
    AddType audio/mpeg .mp3 .m4a
    AddType video/mp4 .mp4 .m4v
    AddType video/mpeg .mpeg .mpg .mpe
    AddType application/vnd.ms-project .mpp
    AddType application/x-font-otf .otf
    AddType application/vnd.ms-opentype .otf
    AddType application/vnd.oasis.opendocument.database .odb
    AddType application/vnd.oasis.opendocument.chart .odc
    AddType application/vnd.oasis.opendocument.formula .odf
    AddType application/vnd.oasis.opendocument.graphics .odg
    AddType application/vnd.oasis.opendocument.presentation .odp
    AddType application/vnd.oasis.opendocument.spreadsheet .ods
    AddType application/vnd.oasis.opendocument.text .odt
    AddType audio/ogg .ogg
    AddType application/pdf .pdf
    AddType image/png .png
    AddType application/vnd.ms-powerpoint .pot .pps .ppt .pptx
    AddType audio/x-realaudio .ra .ram
    AddType image/svg+xml .svg .svgz
    AddType application/x-shockwave-flash .swf
    AddType application/x-tar .tar
    AddType image/tiff .tif .tiff
    AddType application/x-font-ttf .ttf .ttc
    AddType application/vnd.ms-opentype .ttf .ttc
    AddType audio/wav .wav
    AddType audio/wma .wma
    AddType application/vnd.ms-write .wri
    AddType application/font-woff .woff
    AddType application/font-woff2 .woff2
    AddType image/wepb .webp
    AddType application/vnd.ms-excel .xla .xls .xlsx .xlt .xlw
    AddType application/zip .zip
</IfModule>
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css A31536000
    ExpiresByType text/x-component A31536000
    ExpiresByType application/x-javascript A31536000
    ExpiresByType application/javascript A31536000
    ExpiresByType text/javascript A31536000
    ExpiresByType text/x-js A31536000
    ExpiresByType video/asf A31536000
    ExpiresByType video/avi A31536000
    ExpiresByType image/bmp A31536000
    ExpiresByType application/java A31536000
    ExpiresByType video/divx A31536000
    ExpiresByType application/msword A31536000
    ExpiresByType application/vnd.ms-fontobject A31536000
    ExpiresByType application/x-msdownload A31536000
    ExpiresByType image/gif A31536000
    ExpiresByType application/x-gzip A31536000
    ExpiresByType image/x-icon A31536000
    ExpiresByType image/jpeg A31536000
    ExpiresByType application/json A31536000
    ExpiresByType application/vnd.ms-access A31536000
    ExpiresByType audio/midi A31536000
    ExpiresByType video/quicktime A31536000
    ExpiresByType audio/mpeg A31536000
    ExpiresByType video/mp4 A31536000
    ExpiresByType video/mpeg A31536000
    ExpiresByType application/vnd.ms-project A31536000
    ExpiresByType application/x-font-otf A31536000
    ExpiresByType application/vnd.ms-opentype A31536000
    ExpiresByType application/vnd.oasis.opendocument.database A31536000
    ExpiresByType application/vnd.oasis.opendocument.chart A31536000
    ExpiresByType application/vnd.oasis.opendocument.formula A31536000
    ExpiresByType application/vnd.oasis.opendocument.graphics A31536000
    ExpiresByType application/vnd.oasis.opendocument.presentation A31536000
    ExpiresByType application/vnd.oasis.opendocument.spreadsheet A31536000
    ExpiresByType application/vnd.oasis.opendocument.text A31536000
    ExpiresByType audio/ogg A31536000
    ExpiresByType application/pdf A31536000
    ExpiresByType image/png A31536000
    ExpiresByType application/vnd.ms-powerpoint A31536000
    ExpiresByType audio/x-realaudio A31536000
    ExpiresByType image/svg+xml A31536000
    ExpiresByType application/x-shockwave-flash A31536000
    ExpiresByType application/x-tar A31536000
    ExpiresByType image/tiff A31536000
    ExpiresByType application/x-font-ttf A31536000
    ExpiresByType application/vnd.ms-opentype A31536000
    ExpiresByType audio/wav A31536000
    ExpiresByType audio/wma A31536000
    ExpiresByType application/vnd.ms-write A31536000
    ExpiresByType application/font-woff A31536000
    ExpiresByType application/font-woff2 A31536000
    ExpiresByType image/wepb A31536000
    ExpiresByType application/vnd.ms-excel A31536000
    ExpiresByType application/zip A31536000
</IfModule>
<IfModule mod_deflate.c>
    <IfModule mod_headers.c>
        Header append Vary User-Agent env=!dont-vary
    </IfModule>
    <IfModule mod_filter.c>
        AddOutputFilterByType DEFLATE text/css text/x-component application/x-javascript application/javascript text/javascript text/x-js text/html text/richtext image/svg+xml text/plain text/xsd text/xsl text/xml image/x-icon application/json
    <IfModule mod_mime.c>
        # DEFLATE by extension
        AddOutputFilter DEFLATE js css htm html xml
    </IfModule>
    </IfModule>
</IfModule>
<FilesMatch "\.(css|htc|less|js|js2|js3|js4|CSS|HTC|LESS|JS|JS2|JS3|JS4)$">
    FileETag MTime Size
    <IfModule mod_headers.c>
        Header set Pragma "public"
        Header append Cache-Control "public"
         Header unset Set-Cookie
    </IfModule>
</FilesMatch>
<FilesMatch "\.(html|htm|rtf|rtx|svg|svgz|txt|xsd|xsl|xml|HTML|HTM|RTF|RTX|SVG|SVGZ|TXT|XSD|XSL|XML)$">
    FileETag MTime Size
    <IfModule mod_headers.c>
        Header set Pragma "no-cache"
        Header set Cache-Control "max-age=0, private, no-store, no-cache, must-revalidate"
         Header unset Last-Modified
    </IfModule>
</FilesMatch>
<FilesMatch "\.(asf|asx|wax|wmv|wmx|avi|bmp|class|divx|doc|docx|eot|exe|gif|gz|gzip|ico|jpg|jpeg|jpe|json|mdb|mid|midi|mov|qt|mp3|m4a|mp4|m4v|mpeg|mpg|mpe|mpp|otf|odb|odc|odf|odg|odp|ods|odt|ogg|pdf|png|pot|pps|ppt|pptx|ra|ram|svg|svgz|swf|tar|tif|tiff|ttf|ttc|wav|wma|wri|woff|woff2|webp|xla|xls|xlsx|xlt|xlw|zip|ASF|ASX|WAX|WMV|WMX|AVI|BMP|CLASS|DIVX|DOC|DOCX|EOT|EXE|GIF|GZ|GZIP|ICO|JPG|JPEG|JPE|JSON|MDB|MID|MIDI|MOV|QT|MP3|M4A|MP4|M4V|MPEG|MPG|MPE|MPP|OTF|ODB|ODC|ODF|ODG|ODP|ODS|ODT|OGG|PDF|PNG|POT|PPS|PPT|PPTX|RA|RAM|SVG|SVGZ|SWF|TAR|TIF|TIFF|TTF|TTC|WAV|WMA|WRI|WOFF|WOFF2|WEBP|XLA|XLS|XLSX|XLT|XLW|ZIP)$">
    FileETag MTime Size
    <IfModule mod_headers.c>
        Header set Pragma "public"
        Header append Cache-Control "public"
         Header unset Set-Cookie
    </IfModule>
</FilesMatch>

### end ugm theme
';
	}

	private function locate_file( $filename ) {
		$search = array(
			ABSPATH . $filename,
			dirname( ABSPATH ) . DIRECTORY_SEPARATOR . $filename
		);
		foreach ( $search as $path ) {
			if ( file_exists( $path ) ) {
				return $path;
			}
		}
		return false;
	}

}
new UGM_Config_Writer();