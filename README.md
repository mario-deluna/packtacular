Packtacular
===========

Cache is maybe the wrong word, its more a packer, but a pretty cool packer^^

Packtacular packs css files, javascript files, less files or whatever you want together and creates a new packed file that updateds each time you make any changes on your source files. You can easy apply filters to for example minify your css or compile your less code. 

The use Packtacular you need php5.3


How to Use
----------

Lets say we have the following folder structure 

	| index.html
	| lib
	- | packtacular.php
	| cache
	- | css
	| css
	- | some.css
	- | ofmy.css
	- | cssfiles.css
 
Then create a new php file wich schould return the packed content. For example stylesheet.php 

In that file first of all include the packtacular class. But maybe ifyou want to use the Packtacular for only 1 folder i would recommend to just copy paste the packtacular class at the bottom of your script :)

	require "lib/packtacular.php";

and the followig line will print out all content of your css files in the "css/" folder

	Packtacular::css( 'css/', 'cache/css/' );

was that not easy?

	Packtacular::<type>( '<source>', '<target>' );
	
 - type defines for what files Packtacular should search.
 - source, you can set here an array with files or a path to folder. If you set the path to a folder Packtacular is going to search trough that folder recursive for files of the selected type. A folder path should always end with "/"!
 - target defines where to store the cache files, if you set here a path to a folder its going to create new file each time you make changes on your source files.
 
#### Example with a fixed array

	Packtacular::js( array(
		'js/jquery.js',
		'js/myscript.js',
	), 'cache/js/' );
	
#### Example with a fixed cache file

	Packtacular::css( 'css/', 'cache/css/mycachefile.css' );
	
You can also just return the output and do the awesome stuff you want with it :)
	
	$output = Packtacular::css( 'css/', 'cache/css/', true );
	

Filters
-------

These damn little filters can be freakin useful xD

creating a filter is also pretty easy :) ( Damn i think i use the word easy and simple to much >.< )

	Packtacular::filter( '<type>', function( $data ) {
		
		// do something with $data
	
		return $data;
	});


#### Example for usage with CSSMin

	Packtacular::filter( 'css', function( $css ) {
	
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
