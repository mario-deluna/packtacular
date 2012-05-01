<?php 
/**
 * Le easy cache
 *
 * easyCache is maybe the wrong name its more a packer ^.^
 *
 * You need PHP5.3 to use this!
 *
 * @package 		EasyCache
 * @author     		Mario D�ring <mariodoering@me.com>
 * @version 		0.1
 * @copyright 		2012 Mario D�ring
 *
 */
header("Content-type: text/css; charset: UTF-8"); 
require "../lib/easyCache.php";

/*
 * registering filters
 * easyCache::filter( <type>, <filter> );
 */
easyCache::filter( 'css', function( $css ) {
	
	/*
	 * example cssmin implementation 
	 */
	$filters = array (
        "ImportImports"                 => false,
        "RemoveComments"                => true, 
        "RemoveEmptyRulesets"           => true,
        "RemoveEmptyAtBlocks"           => true,
        "ConvertLevel3AtKeyframes"      => true,
        "ConvertLevel3Properties"       => true,
        "Variables"                     => true,
        "RemoveLastDelarationSemiColon" => true
    );
	
	// require CSS min
	require_once "../lib/cssmin-v3.0.1-minified.php";
	
	return CssMin::minify( $css, $filters );
});
 
/*
 * example with a folder
 * easyCache::<filetype>( <dir>|<files_array>, <target_dir>/<target_file> );
 *
 * i hope this is not confusing, if you set a folder as source easyCache is going to
 * search in that dir for all files of that type. If you pass an array its only going to use the passed files.
 *
 * if you set an directory as target easyCache is going to create a new file on every change of your source files
 * if you set a file as target its always writing in that file.
 */
easyCache::css( __DIR__.'/', '../cache/css/' );

