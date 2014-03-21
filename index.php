<?php
/**
 * This is a simple demo how you can use packtacular 
 */
  
// first of all we implement the composer autoloader
require __DIR__.'/vendor/autoload.php';

// set packtacular public directory
Packtacular::storage( __DIR__.'/' );

var_dump( (string) Packtacular::css( 'assets/css/', 'assets/c/stylesheet_:time.css' ) );