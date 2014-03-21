<?php
/**
 * This is a simple demo how you can use packtacular 
 */
  
// first of all we implement the composer autoloader
require __DIR__.'/vendor/autoload.php';

// set packtacular public directory
Packtacular::storage( __DIR__.'/assets/' );

Packtacular::css( 'css/', 'stylesheet.css' );