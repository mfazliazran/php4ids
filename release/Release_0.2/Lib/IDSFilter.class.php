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
 * @subpackage Filter
 */

/**
 * Abstract filter class
 *
 * A basic implementation of a filter object
 *
 * @author Stefan Gehrig (gehrig@ishd.de)
 * @package PHP4IDS
 * @subpackage Filter
 * @abstract
 * @version	0.1
 */
class IDSFilter
{
	/**
	 * Filter rule
	 *
	 * @access 	private
	 * @var 	mixed
	 */
	var $mRule;
	/**
	 * List of tags of the filter
	 *
	 * @access 	private
	 * @var 	array
	 */
	var $mTags=array();
	/**
	 * Filter impact level
	 *
	 * @access 	private
	 * @var 	int
	 */
	var $mImpact=0;
	/**
	 * Filter description
	 *
	 * @access 	private
	 * @var 	string
	 */
	var $mDescription;

	/**
	 * Constructor
	 *
	 * @access 	public
	 * @param	mixed $rule				Filter rule
	 * @param	string $description		Filter description
	 * @param	array $tags				List of tags
	 * @param	integer $impact			Filter impact level
	 * @return 	void
	 */
	function IDSFilter($rule, $description, $tags, $impact)
	{
		$this->mRule=$rule;
		$this->mTags=$tags;
		$this->mImpact=$impact;
		$this->mDescription=$description;
	}

	/**
	 * Abstract match method
	 *
	 * The concrete match process which returns a boolean to inform
	 * about a match
	 *
	 * @abstract
	 * @access 	public
	 * @return	bool
	 */
	function Match($string)
	{
		error("This method needs to be overridden in subclasses");
	}

	/**
	 * Get filter description
	 *
	 * @access 	public
	 * @return	string	Filter description
	 */
	function Description() { return $this->mDescription; }
	/**
	 * Return list of tags
	 *
	 * @access 	public
	 * @return	array	List of tags
	 */
	function Tags() { return $this->mTags; }
	/**
	 * Return filter rule
	 *
	 * @access 	public
	 * @return	mixed	Filter rule
	 */
	function Rule() { return $this->mRule; }
	/**
	 * Get filter impact level
	 *
	 * @access 	public
	 * @return	integer	Impact level
	 */
	function Impact() { return $this->mImpact; }
}

/**
 * Regex filter class
 *
 * The filter class based on regular expression matching is the default
 * filter class used in PHP4IDS.
 *
 * @author Stefan Gehrig (gehrig@ishd.de)
 * @package PDP4IDS
 * @subpackage Filter
 * @version	0.2
 */
class IDSRegexpFilter extends IDSFilter
{
	/**
	 * Returns PCRE flags (default 'ims')
	 *
	 * @access 	public
	 * @package PHP4IDS
	 * @return string
	 */
	function Flags()
	{
		$static_name="_STATIC_" . strtoupper(__CLASS__);
		if (!isset($GLOBALS[$static_name])) $GLOBALS[$static_name]='ims';
		return $GLOBALS[$static_name];
	}

	/**
	 * Set PCRE flags
	 *
	 * @access 	public
	 * @param string $flags Regular expression modifier flags
	 * @return void
	 */
	function SetFlags($flags)
	{
		$static_name="_STATIC_" . strtoupper(__CLASS__);
		$GLOBALS[$static_name]=$flags;
	}

	/**
	 * Match method
	 *
	 * IDSRegexpFilter->match() used preg_match() to match the rule against
	 * the given string.
	 *
	 * @access 	public
	 * @return	bool Filter matched?
	 */
	function Match($string)
	{
		if (!is_string($string)) error('Invalid argument. Exptected a string, got ' . gettype($string));
		return (bool) preg_match('/' . $this->Rule() . '/' . IDSRegexpFilter::Flags(), $string);
	}
}
?>