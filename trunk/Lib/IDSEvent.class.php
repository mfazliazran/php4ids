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
 */

require_once(dirname(__FILE__) . "/IDSFilter.class.php");

/**
 * PHP4IDS event object
 *
 * This class represents a certain event which has been occured while applying
 * the filters to the given data. It aggregates a bunch of IDSFilter
 * implementations and is a assembled in IDSReport.
 *
 * @author Stefan Gehrig (gehrig@ishd.de)
 * @package PHP4IDS
 * @version	0.1
 */
class IDSEvent
{
	/**
	 * Event name
	 *
	 * @access private
	 * @var scalar
	 */
	var $mName=null;
	/**
	 * Value the filter has been applied
	 *
	 * @access private
	 * @var scalar
	 */
	var $mValue=null;
	/**
	 * List of filters
	 *
	 * @access private
	 * @var array
	 */
	var $mFilters=array();
	/**
	 * Computed impact
	 *
	 * @access private
	 * @var integer|bool
	 */
	var $mImpact=null;
	/**
	 * Assembled tags
	 *
	 * @access private
	 * @var array
	 */
	var $mTags=null;

	/**
	 * Generate a new IDS event
	 *
	 * You need to pass the event name (most of the time the name of the key in the
	 * array you have filtered), the value the filters have been applied on and a
	 * list of filters.
	 *
	 * @access public
	 * @param scalar $name
	 * @param scalar $value
	 * @param value $filters
	 */
	function IDSEvent($name, $value, $filters)
	{
		if (!is_scalar($name)) error('Expected $name to be a scalar, ' . gettype($name) . ' given');
		$this->mName=$name;

		if (!is_scalar($value)) error('Expected $value to be a scalar, ' . gettype($value) . ' given');
		$this->mValue=$value;

		for ($i=0; $i<count($filters); $i++)
		{

			if (!is_a($filters[$i], "IDSFilter")) error('Filter must be derived from IDSFilter');
			$this->mFilters[]=&$filters[$i];
		}
	}

	/**
	 * Get event name
	 *
	 * Returns the name of the event (most of the time the name of the filtered
	 * key of the array)
	 *
	 * @access public
	 * @return scalar
	 */
	function Name() { return $this->mName; }

	/**
	 * Get event value
	 *
	 * Returns the value which has been passed to the filters
	 *
	 * @access public
	 * @return scalar
	 */
	function Value() { return $this->mValue; }

	/**
	 * Get computed impact
	 *
	 * Returns the overal impact of all filters
	 *
	 * @access public
	 * @return integer
	 */
	function Impact()
	{
		if (is_null($this->mImpact))
		{
			$this->mImpact=0;
			for ($i=0; $i<count($this->mFilters); $i++)
			{
				$this->mImpact+=$this->mFilters[$i]->Impact();
			}
		}
		return $this->mImpact;
	}

	/**
	 * Get assembled tags
	 *
	 * Collects all the tags of the filters
	 *
	 * @access public
	 * @return array
	 */
	function Tags()
	{
		if (is_null($this->mTags))
		{
			$this->mTags=array();
			for ($i=0; $i<count($this->mFilters); $i++)
			{
				$this->mTags=array_merge($this->mTags, $this->mFilters[$i]->Tags());
			}
			$this->mTags=array_values(array_unique($this->mTags));
		}
		return $this->mTags;
	}

	/**
	 * Get list of filters
	 *
	 * @access public
	 * @return array
	 */
	function Filters() { return $this->mFilters; }

	/**
	 * Get number of filters
	 *
	 * @access public
	 * @return integer
	 */
	function Count() { return count($this->mFilters); }
}
?>