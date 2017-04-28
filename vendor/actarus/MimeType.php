<?php

namespace Actarus;


class MimeType
{
	const DEFAULT_MIME_TYPE = 'application/octet-stream';
	
	
	public static function getMimeType( $extension ) {
		$t_list = self::getList();
		if( isset($t_list[$extension]) ) {
			return $t_list[$extension];
		} else {
			self::DEFAULT_MIME_TYPE;
		}
	}
	
	
	public static function getExtension( $mime_type ) {
		$t_list = self::getList();
		$ext = array_search( $mime_type, $t_list );
		if( $ext === false ) {
			$ext = '';
		}
		return $ext;
	}
	
	
	public static function getList()
	{
		return array(
			'ai'    => 'application/postscript',
			'aif'   => 'audio/x-aiff',
			'aifc'  => 'audio/x-aiff',
			'aiff'  => 'audio/x-aiff',
			'asc'   => 'text/plain',
			'au'    => 'audio/basic',
			'avi'   => 'video/x-msvideo',
			'bcpio' => 'application/x-bcpio',
			'bin'   => 'application/octet-stream',
			'bmp'   => 'image/bmp',
			'cdf'   => 'application/x-netcdf',
			'class' => 'application/octet-stream',
			'cpio'  => 'application/x-cpio',
			'cpt'   => 'application/mac-compactpro',
			'csh'   => 'application/x-csh',
			'css'   => 'text/css',
			'dcr'   => 'application/x-director',
			'dir'   => 'application/x-director',
			'djv'   => 'image/vnd.djvu',
			'djvu'  => 'image/vnd.djvu',
			'dll'   => 'application/octet-stream',
			'dms'   => 'application/octet-stream',
			'doc'   => 'application/msword',
			'dvi'   => 'application/x-dvi',
			'dxr'   => 'application/x-director',
			'eps'   => 'application/postscript',
			'etx'   => 'text/x-setext',
			'exe'   => 'application/octet-stream',
			'ez'    => 'application/andrew-inset',
			'gif'   => 'image/gif',
			'gtar'  => 'application/x-gtar',
			'hdf'   => 'application/x-hdf',
			'hqx'   => 'application/mac-binhex40',
			'html'  => 'text/html',
			'htm'   => 'text/html',
			'ice'   => 'x-conference-xcooltalk'
			'ief'   => 'image/ief',
			'iges'  => 'model/iges',
			'igs'   => 'model/iges',
			'jpeg'  => 'image/jpeg',
			'jpe'   => 'image/jpeg',
			'jpg'   => 'image/jpeg',
			'js'    => 'application/x-javascript',
			'kar'   => 'audio/midi',
			'latex' => 'application/x-latex',
			'lha'   => 'application/octet-stream',
			'lzh'   => 'application/octet-stream',
			'm3u'   => 'audio/x-mpegurl',
			'man'   => 'application/x-troff-man',
			'me'    => 'application/x-troff-me',
			'mesh'  => 'model/mesh',
			'mid'   => 'audio/midi',
			'midi'  => 'audio/midi',
			'movie' => 'video/x-sgi-movie',
			'mov'   => 'video/quicktime',
			'mp2'   => 'audio/mpeg',
			'mp3'   => 'audio/mpeg',
			'mpeg'  => 'video/mpeg',
			'mpe'   => 'video/mpeg',
			'mpga'  => 'audio/mpeg',
			'mpg'   => 'video/mpeg',
			'ms'    => 'application/x-troff-ms',
			'msh'   => 'model/mesh',
			'mxu'   => 'video/vnd.mpegurl',
			'nc'    => 'application/x-netcdf',
			'oda'   => 'application/oda',
			'pbm'   => 'image/x-portable-bitmap',
			'pdb'   => 'chemical/x-pdb',
			'pdf'   => 'application/pdf',
			'pgm'   => 'image/x-portable-graymap',
			'pgn'   => 'application/x-chess-pgn',
			'png'   => 'image/png',
			'pnm'   => 'image/x-portable-anymap',
			'ppm'   => 'image/x-portable-pixmap',
			'ps'    => 'application/postscript',
			'qt'    => 'video/quicktime',
			'ra'    => 'audio/x-realaudio',
			'ram'   => 'audio/x-pn-realaudio',
			'ras'   => 'image/x-cmu-raster',
			'rgb'   => 'image/x-rgb',
			'rm'    => 'audio/x-pn-realaudio',
			'roff'  => 'application/x-troff',
			'rpm'   => 'audio/x-pn-realaudio-plugin',
			'rtf'   => 'text/rtf',
			'rtx'   => 'text/richtext',
			'sgml'  => 'text/sgml',
			'sgm'   => 'text/sgml',
			'sh'    => 'application/x-sh',
			'shar'  => 'application/x-shar',
			'silo'  => 'model/mesh',
			'sit'   => 'application/x-stuffit',
			'skd'   => 'application/x-koan',
			'skm'   => 'application/x-koan',
			'skp'   => 'application/x-koan',
			'skt'   => 'application/x-koan',
			'smi'   => 'application/smil',
			'smil'  => 'application/smil',
			'snd'   => 'audio/basic',
			'so'    => 'application/octet-stream',
			'spl'   => 'application/x-futuresplash',
			'src'   => 'application/x-wais-source',
			'sv4cpio' => 'application/x-sv4cpio',
			'sv4crc' => 'application/x-sv4crc',
			'swf'   => 'application/x-shockwave-flash',
			't'     => 'application/x-troff',
			'tar'   => 'application/x-tar',
			'tcl'   => 'application/x-tcl',
			'tex'   => 'application/x-tex',
			'texi'  => 'application/x-texinfo',
			'texinfo' => 'application/x-texinfo',
			'tiff'  => 'image/tiff',
			'tif'   => 'image/tif',
			'tr'    => 'application/x-troff',
			'tsv'   => 'text/tab-seperated-values',
			'txt'   => 'text/plain',
			'ustar' => 'application/x-ustar',
			'vcd'   => 'application/x-cdlink',
			'vrml'  => 'model/vrml',
			'wav'   => 'audio/x-wav',
			'wbmp'  => 'image/vnd.wap.wbmp',
			'wbxml' => 'application/vnd.wap.wbxml',
			'wmlc'  => 'application/vnd.wap.wmlc',
			'wmlsc' => 'application/vnd.wap.wmlscriptc',
			'wmls'  => 'text/vnd.wap.wmlscript',
			'wml'   => 'text/vnd.wap.wml',
			'wrl'   => 'model/vrml',
			'xbm'   => 'image/x-xbitmap',
			'xht'   => 'application/xhtml+xml',
			'xhtml' => 'application/xhtml+xml',
			'xml'   => 'text/xml',
			'xpm'   => 'image/x-xpixmap',
			'xsl'   => 'text/xml',
			'xwd'   => 'image/x-windowdump',
			'xyz'   => 'chemical/x-xyz',
			'zip'   => 'application/zip',
		);
	}
}
