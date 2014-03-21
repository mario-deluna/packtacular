<?php 
/**
 * Packtacular Filter Interface
 **
 *
 * @package 		Packtacular
 * @author		Mario Döring <mario@clan-cats.com>
 * @version		2.0
 * @copyright 	2010 - 2014 ClanCats GmbH
 *
 */
interface Packtacular_Filter_Interface
{
	/**
	 * Return the filter type css, js, etc..
	 *
	 * @return string
	 */
	public function type();
	
	/**
	 * Does this filter apply for each file individually?
	 *
	 * @return bool
	 */
	public function singleton();
	
	/**
	 * Does this filter apply for each file individually?
	 *
	 * @param string 		$buffer
	 * @param string			$file_name
	 * @return string
	 */
	public function filter( $buffer, $file_name = null );
}