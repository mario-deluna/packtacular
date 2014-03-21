<?php 
/**
 * Packtacular main
 **
 *
 * @package 		Packtacular
 * @author		Mario Döring <mario@clan-cats.com>
 * @version		2.0
 * @copyright 	2010 - 2014 ClanCats GmbH
 *
 */
class Packtacular 
{
	/**
	 * The filters holder
	 *
	 * @var array
	 */
	protected static $filters = array();

	/**
	 * Adds an packtacular filter
	 *
	 * Packtacular::filter( <type>, <filter> );
	 *
	 * Example:
	 *     Packtacular::filter( 'css', function( $css ) {
	 *         return $css;	 
	 *     });
	 * 
	 * @param string 	$type		What should be filtered: css, js, less etc.
	 * @param mixed		$callback	A closure or array can be passed
	 */
	public static function filter( $type, $callback ) 
	{
		static::$filters[$type][] = $callback;
	}

	/**
	 * The public directory where packtacular can get an store data
	 *
	 * @var string
	 */
	protected static $storage = null;
	
	/**
	 * Set the active public storage path
	 *
	 * Example:
	 *     Packtacular::storage( $_SERVER['DOCUMENT_ROOT'].'/assets/' );
	 *
	 * @param string		$storage		This should be an absoulute path to your public directory
	 * @return void
	 */
	public static function storage( $storage ) 
	{
		static::$storage = $storage;
	}
	
	/**
	 * So let's get the party startet with the magic method
	 *
	 * 
	 *
	 * @param string 	$type
	 * @param array		$arguments
	 * @return string
	 */
	public static function __callStatic( $type, $arguments ) 
	{
		$source = $arguments[0];
		$target = $arguments[1];

		/*
		 * get source files
		 */
		if ( !is_array( $source ) ) {

			if ( strpos( $source, '.'.$type ) !== false ) {
				$source = array( $source );
			}
			elseif ( substr( $source, -1 ) == '/' ) {
				$source = static::files_of_type( $type, $source );
			}
			else {
				throw new packtacularException( "Packtacular - Could not match your source :(" );
			}
		}

		// check if empty 
		if ( empty( $source ) ) {
			throw new packtacularException( "Packtacular - No source files found!" );
		}

		// get last modified timestamp
		$last_change = static::last_change( $source );

		// check if target is a directory
		if ( substr( $target, -1 ) == '/' ) {
			$target = $target.static::$settings['file_prefix'].$last_change.static::$settings['file_suffix'];
		}

		$buff_target_time = filemtime( $target ) + static::$settings['time_buffer'];

		// check if the cache is valid
		if ( !file_exists( $target ) ||  $last_change > $buff_target_time ) {
			if ( !static::write_cache( $source, $target, $type ) ) {
				throw new packtacularException( "Packtacular - Writing the file to <{$target}> faild!" );
			}
		}

		// return the contetns 
		if ( $return ) {
			return file_put_contents( $target );
		}

		/*
		 * set header to the given type
		 */
		if ( static::$settings['auto_header'] ) {
			header("Content-type: text/$type; charset: UTF-8"); 
		}

		/*
		 * output that stuff 
		 */
		ob_clean();
		flush();
		readfile( $target );
	} 
	
	/**
	 * An array containig the source files
	 *
	 * @var array
	 */
	protected $sources = null;
	
	/**
	 * The cache target 
	 *
	 * @var string
	 */
	protected $target =  null;
	
	/**
	 * The cache type like css, js etc.
	 *
	 * @var string
	 */
	protected $type = null;
	
	/**
	 * Packtacular constructor
	 *
	 * @param string				$type
	 * @param array|string		$source
	 * @param string				$targer
	 * @return void
	 */
	public function __construct( $type, $source, $target ) 
	{
		// we can assign the type and the target directly
		$this->type = $type;
		$this->target = $target;
		
		// did we got an array of files or an directory?
		if ( is_array( $source ) )
		{
			$this->sources = $source;
		}
		else 
		{
			// but if our source is a string and not an array
			// we assume that we got an directory passed and get all 
			// matching files from that directory.
			if ( !is_dir( $source ) ) 
			{
				throw new Packtacular_Exception( "Packtacular - The source direcotry is not readable." );
			}
			
			// lets check if we go
			$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir ), RecursiveIteratorIterator::SELF_FIRST );
			
			$this->sources = array();
			
			foreach ( $iterator as $file ) 
			{
				$file = $file->getPathname();
				
				if ( substr( $file, ( strlen( '.'.$type ) * -1 ) ) === '.'.$type ) 
				{
					$this->sources[] = $file;
				}
			}
		}
	}
	
	/**
	 * get all files in dir
	 *
	 * @param string 	$type
	 * @param string	$dir
	 * @return array
	 */
	protected static function files_of_type( $type, $dir ) {

		if ( !file_exists( $dir ) ) 
		{
			throw new packtacularException( "Packtacular - The source directory does not exists!" );
		}

		$dir_iterator = new RecursiveDirectoryIterator( $dir );
		$iterator = new RecursiveIteratorIterator( $dir_iterator, RecursiveIteratorIterator::SELF_FIRST );

		$files = array();

		foreach ( $iterator as $file ) {

			$file = $file->getPathname();

			if ( strpos( $file, '.'.$type ) !== false  ) {
				$files[] = $file;
			}
		}

		return $files;
	}

	/**
	 * get the last changed date of an array of files
	 *
	 * @param array		$files
	 * @return int
	 */
	protected static function last_change( $files ) {
		foreach( $files as $key => $file ) {
			$files[$key] = filemtime( $file );
		}

		sort( $files );  $files = array_reverse( $files );

		return $files[key($files)];
	}

	/**
	 * Write cache apply filters
	 * 
	 * @param array		$files
	 * @param string 	$target
	 * @param string	$type
	 * @return bool
	 */
	protected static function write_cache( $files, $target, $type ) {

		$content = "";

		foreach( $files as $file ) {
			$content .= file_get_contents( $file );
		}

		foreach( static::$filters[$type] as $filter ) {
			$content = call_user_func_array( $filter, array( $content ) );
		}

		return file_put_contents( $target, $content );
	}
}