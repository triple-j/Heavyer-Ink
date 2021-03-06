<?php
require('includes/app_top.php');
include('includes/minify/CSSmin.php');
include('includes/minify/JSMin.php');

#if ( isset($_COOKIE['HERI_REQUEST']) ) {
#	$_REQUEST = json_decode( $_COOKIE['HERI_REQUEST'], true );
#	#setcookie("HERI_REQUEST", "", time() - 3600); // delete cookie
#}

if ( isset($_SESSION['HERI_REQUEST']) ) {
	$_REQUEST = json_decode( $_SESSION['HERI_REQUEST'], true );
	#unset($_SESSION['HERI_REQUEST']);
}


$cssmin = new CSSmin();

$extensions = isset($_REQUEST['extensions'])?$_REQUEST['extensions']:$heri_default_extensions;
$extensions_data = array();

$is_default  = (bool)( identical_values($heri_default_extensions, $extensions) );
$custom_hash = md5( HERI_VERSION . implode('',$extensions) );


foreach ( $extensions as $extension ) {
	$ext_dir = DIR_EXTENSIONS.$extension."/";
	$xmlfile = $ext_dir."extension.xml";

	if ( file_exists($xmlfile) ) {
		$xmlcontents = file_get_contents( $xmlfile );
		$xml = new SimpleXMLElement( $xmlcontents );

		$css_count = $xml->stylesheet->count();
		$js_count  = $xml->javascript->count();

		if ( $css_count ) {
			$stylesheets = ($css_count > 1) ? (array)$xml->stylesheet : array( (string)$xml->stylesheet );

			foreach ( $stylesheets as $stylesheet ) {
				if ( !isset($extensions_data['styles']) ) $extensions_data['styles'] = array();

				$cssfile = $ext_dir . $stylesheet;
				if ( file_exists($cssfile) ) {
					$filecontents = file_get_contents( $cssfile );
					$minifiedCss = $cssmin->run( $filecontents );
					$extensions_data['styles'] []= $minifiedCss;
				}
			}
		}

		if ( $js_count ) {
			$javascripts = ($js_count > 1) ? (array)$xml->javascript : array( (string)$xml->javascript );

			foreach ( $javascripts as $javascript ) {
				if ( !isset($extensions_data['scripts']) ) $extensions_data['scripts'] = array();

				$jsfile = $ext_dir . $javascript;
				if ( file_exists($jsfile) ) {
					$filecontents = file_get_contents( $jsfile );
					$minifiedJs = str_replace( "\n", "", JSMin::minify($filecontents) );
					$extensions_data['scripts'] []= $minifiedJs;
				}
			}
		}
	}
}

//Set no caching
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

header('Content-type: text/javascript; charset=utf-8');

PX_Template::set_template("heavyerink.user");

PX_Template::set_region('version', HERI_VERSION.($is_default?"":" (".$custom_hash.")"));
PX_Template::set_region('extension_json', json_encode($extensions_data));
@PX_Template::out();

