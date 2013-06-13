<?php 
/**
 * Le easy cache
 *
 * Packtacular is maybe the wrong name its more a packer ^.^
 *
 * You need PHP5.3 to use this!
 *
 * @package 		Packtacular
 * @author     		Mario Döring <mariodoering@me.com>
 * @version 		0.1
 * @copyright 		2013 Mario Döring
 *
 */
class Packtacular {
	
	/*
	 * settings
	 */
	protected static $settings = array(
		
		/*
		 * dont recache if the last modification is smaller then the cache + time_buffer
		 */
		'time_buffer' => 0,
		
		/*
		 * Cache file prefix
		 */
		'file_prefix' => 'cat_',
		
		/*
		 * Cache file suffix
		 */
		'file_suffix' => '.cache',
		
		/*
		 * set Headers automaic
		 * default is false because its not that awesome
		 */
		'auto_header' => false,
	);
	
	/*
	 * filters holder
	 */
	protected static $filters = array();
	
	/**
	 * add a filter 
	 * 
	 * @param string 	$type
	 * @param mixed		$call
	 */
	public static function filter( $type, $call ) {
		static::$filters[$type][] = $call;
	}
	
	/**
	 * Super awesome magic method
	 *
	 * @param string 	$method
	 * @param array		$arguments
	 * @return string
	 */
	static function __callStatic( $type, $arguments ) {
        
		$source = $arguments[0];
		$target = $arguments[1];
		$return = false;
		
		if ( isset( $arguments[2] ) ) {
			$return = $arguments[2];
		}
		
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
	 * get all files in dir
	 *
	 * @param string 	$type
	 * @param string	$dir
	 * @return array
	 */
	protected static function files_of_type( $type, $dir ) {
		
		if ( !file_exists( $dir ) ) {
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

// Exception
class packtacularException extends Exception {}