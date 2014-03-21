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
	 * @param bool		$singleton	When true the filter gets applied for each file instead of the entire pack.
	 */
	public static function filter( $type, $callback, $singleton = false ) 
	{
		if ( !is_callable( $callback ) )
		{
			throw new Packtacular_Exception( "Packtacular - invalid callback for filter given." );
		}
		
		static::$filters[$type][(int) $singleton][] = $callback;
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
	 *     Packtacular::storage( $_SERVER['DOCUMENT_ROOT'] );
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
	 * Packtacular::<filetype>( <dir>|<files_array>, <target_file> );
	 *
	 * Source:
	 * I hope this is not to confusing. If you set a folder as source Packtacular is going to
	 * search in that directory for all files matching the given type. Alternatively	you can
	 * pass an array of files.
	 *
	 * Target:
	 * Define where the cache file should be created. You can simply pass a file path. To bypass
	 * the problem that the browser might cache the files on its own, you can add a timestamp
	 * to the path using the :time parameter.
	 *
	 * Example:
	 *     Packtacular::css( 'css/', 'cache/stylesheet_:time.css' );
	 *   
	 * This would return something like:
	 *     /cache/stylesheet_1395407386.css
	 *
	 * @param string 	$type
	 * @param array		$arguments
	 * @return string
	 */
	public static function __callStatic( $type, $arguments ) 
	{
		return new static( $type, $arguments[0], $arguments[1] );
	} 
	
	/**
	 * An array containig the source files
	 *
	 * @var array
	 */
	protected $sources = array();
	
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
	 * The last modified timestamp of the sources
	 *
	 * @var string
	 */
	protected $last_change = null;
	
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
		elseif ( is_string( $source ) )
		{
			// but if our source is a string and not an array
			// we assume that we got an directory passed and get all 
			// matching files from that directory.
			if ( !$this->is_absolute_path( $source ) )
			{
				$soruce = static::$storage.$source;
			}
			
			$this->directory( $soruce );
		}
		else 
		{
			throw new Packtacular_Exception( "Packtacular - The secound argument should contain valid sources." );
		}
	}
	
	/**
	 * Add files from a directory to the sources
	 *
	 * @param string			$dir
	 * @return void
	 */
	public function directory( $dir )
	{	
		if ( !is_dir( $dir ) ) 
		{
			throw new Packtacular_Exception( "Packtacular - The source direcotry '".$dir."' is not readable." );
		}
		
		// we need to reset the last change timestamp because we might add new files
		$this->reset_last_change();
		
		$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir ), RecursiveIteratorIterator::SELF_FIRST );
		
		foreach ( $iterator as $file ) 
		{
			$file = $file->getPathname();
			
			if ( substr( $file, ( strlen( '.'.$this->type ) * -1 ) ) === '.'.$this->type ) 
			{
				$this->sources[] = $file;
			}
		}
	}
	
	/**
	 * Add files from an array to the packer instance
	 *
	 * @param array		$files
	 * @return void
	 */
	public function files( array $files )
	{
		// we need to reset the last change timestamp because we might add new files
		$this->reset_last_change();
		
		foreach( $files as $file )
		{
			if ( !$this->is_absolute_path( $source ) )
			{
				$file = static::$storage.$file;
			}
			
			$this->sources[] = $file;
		}
	}
	
	/**
	 * Return the cache file path and compile the sources if a change happend
	 *
	 * @param bool		$absolute
	 * @return string
	 */ 
	public function get( $absolute = false )
	{
		$target = $target_path = str_replace( ':time', $this->last_change(), $this->target );
		
		if ( !$this->is_absolute_path( $target_path ) )
		{
			$target_path = static::$storage.$target_path;
		}
		
		// When the target file does not exist or the last change of the target is
		// smaller then the last change of the sources we need to rebuild
		if ( !file_exists( $target_path ) || filemtime( $target_path ) < $this->last_change() )
		{
			$this->build( $target_path );
		}
		
		// make the public target absolute
		if ( !$this->is_absolute_path( $target ) )
		{
			$target = '/'.$target;
		}
		
		return $target;
	}
	
	/**
	 * Php's to string magic so we can get the path directly
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->get();
	}
	
	/**
	 * Re build the cache into a file
	 *
	 * @param string		$target
	 * @return bool
	 */
	public function build( $target )
	{
		// because we never now and every developer is a 
		// bit paranoid we check for dublicate files
		$this->sources = array_unique( $this->sources );
		
		$buffer = "";
		
		// lets get the content of each file an apply the file filters on it
		foreach( $this->sources as $file ) 
		{
			if ( !$content = file_get_contents( $file ) )
			{
				throw new Packtacular_Exception( "Packtacular - cannot read source file: ".$file );
			}
			
			if ( isset( static::$filters[$this->type][1] ) && is_array( static::$filters[$this->type][1] ) )
			{
				foreach( static::$filters[$this->type][1] as $filter ) 
				{
					$content = call_user_func( $filter, $content, $file );
				}
			}
			
			// maybe we got some filter that apply to all file types
			if ( isset( static::$filters['*'][1] ) && is_array( static::$filters['*'][1] ) )
			{
				foreach( static::$filters['*'][1] as $filter ) 
				{
					$content = call_user_func( $filter, $content, $file );
				}
			}
			
			// in case we get an false as response just append the content if we 
			// recive an string after applying the filters.
			if ( is_string( $content ) )
			{
				$buffer .= $content."\n";
			}
		}
		
		// apply the pack filters to the buffer
		if ( isset( static::$filters[$this->type][0] ) && is_array( static::$filters[$this->type][0] ) )
		{
			foreach( static::$filters[$this->type][0] as $filter ) 
			{
				$buffer = call_user_func( $filter, $buffer );
			}
		}
		
		// also apply the any type filters to the buffer
		if ( isset( static::$filters['*'][0] ) && is_array( static::$filters['*'][0] ) )
		{
			foreach( static::$filters['*'][0] as $filter ) 
			{
				$buffer = call_user_func( $filter, $buffer );
			}
		}
		
		// create the missing direcotries
		if ( !is_dir( dirname( $target ) ) ) 
		{
			if ( !mkdir( dirname( $target ), 0755, true ) ) 
			{
				throw new Packtacular_Exception( "Packtacular - could not create directory: ".dirname( $target ) );
			}
		}
		
		return file_put_contents( $target, $buffer );
	}
	
	/**
	 * Check if the path starts with an slash
	 *
	 * @param string			$path
	 * @return bool
	 */
	protected function is_absolute_path( $path )
	{
		return substr( $path, 0, 1 ) === '/';
	}
	
	/** 
	 * Reset the last changed timestamp
	 *
	 * @return void
	 */
	protected function reset_last_change()
	{
		$this->last_change = null;
	}
	
	/**
	 * The last modified timestamp of the sources
	 *
	 * @return int
	 */
	protected function last_change() 
	{
		if ( !is_null( $this->last_change ) )
		{
			return $this->last_change;
		}
		
		foreach( $this->sources as $key => $file ) 
		{
			$files[$key] = filemtime( $file );
		}

		sort( $files );  $files = array_reverse( $files );

		return $this->last_change = $files[key($files)];
	}
}