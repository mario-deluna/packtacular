Packtacular
===========

Packtacular is a fast and easy asset packer, compressor, manager.

We are all busy so we should not waste time dealing with asset packing. Im also going to skip all the "where the idea came from bla bla" so lets directly continue with how to use Packtacular.

**don't forget that you need to grant write permission's for php.**

## Usage

First of all packtacular need's to know where your storage directory ( public directory ) is. 
If you don't set up the storage directory packtacular is going to use the relative path.

```php
Packtacular::storage( $_SERVER['DOCUMENT_ROOT'].'/' );
```

The thing here was "Hey let's keep it easy" so to create your package you only need to do this:

	Packtacular::<type>( '<source>', '<target>' );

_Example:_
	
```php
// When you pass relative paths packtacular is going to prefix your path
// with the storage directory. `<storage>/assets/css/`
Packtacular::css( 'assets/css/', 'cache/stylesheet_:time.css' )
```

This example will return an absolute public path to the package assuming that the target is the public root path.
`/cache/stylesheet_1395407386.css`

### A bit more detail
	
	Packtacular::<filetype>( <dir>|<files_array>, <target_file> );

**Type:**
This means the basically the file extension. `Packtacular::js` will match all `.js` files in the given directory.

**Source:**
I hope this is not to confusing. If you set a folder as source Packtacular is going to
search in that directory for all files matching the given type. Alternatively	you can
pass an array of files.

**Target:**
Define where the cache file should be created. You can simply pass a file path. To bypass
the problem that the browser might cache the files on its own, you can add a timestamp
to the path using the :time parameter.
 
### Example with a fixed array

```php
Packtacular::js( array(
	'js/jquery.js',
	'js/myscript.js',
), 'cache/:time/core.js' );
```
	
## Filters

Filters can catch the file contents before they get packed. Wich gives you the possibility to compress or compile your assets.

```php
Packtacular::filter( '<type>', function( $data ) 
{	
	// do something with $data
	return $data;
});
```

**There are 2 types of filters package and single file filter.**

_The single filter will be applied over every file on it's own._

```php
// This little example will add a comment above each file 
// containing the file path of the current file.
Packtacular::filter( '*', function( $data, $file_name ) 
{	
	return "/* ".$file_name." */\n".$buffer;
}, true );
```

_The normal filter can only modify the entire package._

```php
// This little example will only be executed on the entire package.
Packtacular::filter( 'css', function( $data ) 
{	
	return str_replace( ' ', '', $data );
});
```

## Preset filters

There are some filter that already ship with packtacular you can add them by simply executing:

```php
Packtacular_Filter::add( 'comments' );
```

Following filters are available:

 * `comments`