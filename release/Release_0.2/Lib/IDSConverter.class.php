<?php
/**
 * PHP4IDS
 *
 * Requirements: PHP4, DOM XML
 *
 * Copyright (c) 2007 Stefan Gehrig (gehrig@ishd.de)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; version 2 of the license.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @package PHP4IDS
 * @subpackage Converter
 */

/**
 * PHP4IDS specific utility class to convert charsets manually
 *
 * Note that if you make use of IDS_Converter::runAll(), existing class
 * methods will be executed in the same order as they are implemented in the
 * class tree!
 *
 * @author Stefan Gehrig (gehrig@ishd.de)
 * @package PHP4IDS
 * @subpackage Converter
 * @version	0.2
 */
class IDSConverter
{
	/**
	 * Runs all converter functions
	 *
	 * Note that if you make use of IDSConverter::Convert(), existing class
	 * methods will be executed in the same order as they are implemented in the
	 * class tree!
	 *
	 * @static
     * @access public
     * @param   string  $value
	 * @return	string
	 */
	function Convert($value)
	{
		$methods=get_class_methods(__CLASS__);
		$key=array_search('Convert', $methods);
		unset($methods[$key]);

		foreach ($methods as $key => $func)
		{
			$value=IDSConverter::$func($value);
		}

		return $value;
	}

	/**
     * Converts listed UTF-7 tags to UTF-8
     *
     * @static
     * @access public
     * @param string  $data
     * @return string converted $data
    */
	function ConvertFromUTF7($data)
	{
		$schemes=array
		(
		'+AFwAIg'  => '"',
		'+ADw-'     => '<',
		'+AD4-'     => '>',
		'+AFs'     => '[',
		'+AF0'     => ']',
		'+AHs'     => '{',
		'+AH0'     => '}',
		'+AFw'     => '\\',
		'+ADs'     => ';',
		'+ACM'     => '#',
		'+ACY'     => '&',
		'+ACU'     => '%',
		'+ACQ'     => '$',
		'+AD0'     => '=',
		'+AGA'     => '`',
		'+ALQ'     => '"',
		'+IBg'     => '"',
		'+IBk'     => '"',
		'+AHw'     => '|',
		'+ACo'     => '*',
		'+AF4'     => '^'
		);

		$data=str_replace(array_keys($schemes), array_values($schemes), $data);
        $data=str_replace(array_keys(array_map("strtolower", $schemes)), array_values($schemes), $data);
		return $data;
	}

	/**
      * Checks for common charcode pattern and decodes them
      *
      * @static
      * @access public
      * @param string  $value
      * @return  string $value
      */
	function ConvertFromJSCharcode($value)
	{
		# check if value matches typical charCode pattern
		if (preg_match_all('/(?:[\w+-=\/\* ]*(?:\s?,\s?[\w+-=\/\* ]+)+)/s', $value, $matches))
		{
			$converted='';
			$string=implode(',', $matches[0]);
			$string=preg_replace('/\s/', '', $string);
			$string=preg_replace('/\w+=/', '', $string);
			$charcode=explode(',', $string);

			foreach($charcode as $char)
			{
				$char=preg_replace('/[\W]0/s', '', $char);
				if(preg_match_all('/\d*[+-\/\* ]\d+/', $char, $matches))
				{
					$match=preg_split('/([\W]?\d+)/', (implode('', $matches[0])), null, PREG_SPLIT_DELIM_CAPTURE);
					$converted.=chr(array_sum($match));

				}
				else if(!empty($char)) $converted.=chr($char);
			}
			$value.="\n[" . $converted . "] ";
		}

		# check for octal charcode pattern
		if (preg_match_all('/(?:(?:[\\\]+\d+\s*){2,})/ims', $value, $matches))
		{
			$converted='';
			$charcode=explode('\\', preg_replace('/\s/', '', implode(',', $matches[0])));
			foreach($charcode as $char)
			{
				if(!empty($char)) $converted .= chr(octdec($char));
			}
			$value.="\n[" . $converted . "] ";
		}

		# check for hexadecimal charcode pattern
		if (preg_match_all('/(?:(?:[\\\]+\w+\s*){2,})/ims', $value, $matches))
		{
			$converted='';
			$charcode=explode('\\', preg_replace('/[ux]/', '', implode(',', $matches[0])));
			foreach($charcode as $char)
			{
				if(!empty($char)) $converted .= chr(hexdec($char));
			}
			$value.="\n[" . $converted . "] ";
		}
		return $value;
	}

	/**
     * Check for comments and erases them if available
     *
     * @static
     * @access public
     * @param string  $value
     * @return  string  $value
     */
	function ConvertFromCommented($value)
	{
		# check for existing comments
		if (preg_match('/(?:\<!-|-->|\/\*|\*\/|\/\/\W*\w+\s*$)|(?:(?:#|--|{)\s*$)/ms', $value))
		{
			$pattern=array(
				'/(?:(?:<!)(?:(?:--(?:[^-]*(?:-[^-]+)*)--\s*)*)(?:>))/ms',
				'/(?:(?:\/\*\/*[^\/\*]*)+\*\/)/ms',
				'/(?:(?:\/\/|--|#|{).*)/ms'
			);
			$converted=preg_replace($pattern, null, $value);
			$value.="\n[" . $converted . "] ";
		}
		return $value;
	}

	/**
     * Normalize quotes
     *
     * @static
     * @access public
     * @param string  $value
     * @return  string  $value
     */
	function ConvertQuotes($value)
	{
		# normalize different quotes to "
		$pattern=array('\'', '`', '´', '’', '‘');
		$value=str_replace($pattern, '"', $value);
		return $value;
	}

	/**
     * Converts basic concatenations
     *
     * @static
     * @access public
     * @param string  $value
     * @return  string  $value
     */
	function ConvertConcatenations($value)
	{
		$compare = '';
		if (get_magic_quotes_gpc()) $compare=stripslashes($value);
		$pattern=array(
			'/("\s*[\W]+\s*\n*")*/ms',
			'/(";\w\s*+=\s*\w?\s*\n*")*/ms',
			'/("[|&;]+\s*[^|&\n]*[|&]+\s*\n*"?)*/ms',
			'/(";\s*\w+\W+\w*\s*[|&]*")*/ms',
			'/(?:"?\+[^"]*")/ms'
		);
		# strip out concatenations
		$converted = preg_replace($pattern, null, $compare);
		if ($compare != $converted) $value .= "\n[" . $converted . "] ";
		return $value;
	}

	/**
     * Converts from hex/dec entities
     *
     * @static
     * @access public
     * @param string  $value
     * @return  string  $value
     */
	function ConvertEntities($value)
	{
		$converted = '';
		if(preg_match('/&#x?[\w]+;/ms', $value))
		{
			$converted=html_entity_decode($value);
			$value.="\n[" . $converted . "] ";
		}
		return $value;
	}
}
?>