<?php 
/**
 * Packtacular Filter 
 * This is a little helper to register the with packtacular shipped filters
 **
 *
 * @package 		Packtacular
 * @author		Mario Döring <mario@clan-cats.com>
 * @version		2.0
 * @copyright 	2010 - 2014 ClanCats GmbH
 *
 */
class Packtacular_Filter 
{
	/**
	 * Add a filter preset to packtacular
	 *
	 * @param string 		$filter
	 * @return void
	 */
	public static function add( $filter )
	{
		$class = get_called_class().'_'.ucfirst( $filter );
		
		if ( !class_exists( $class ) )
		{
			throw new Packtacular_Exception( "Packtacular - There is no preset filter for '".$filter."'." );
		}
		
		$filter = new $class;
		
		// add the filter to packtacular
		Packtacular::filter( $filter->type(), array( $filter, 'filter' ), $filter->singleton() );
	}
}