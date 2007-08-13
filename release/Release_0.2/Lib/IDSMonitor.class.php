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

require_once(dirname(__FILE__) . "/IDSStorageProvider.class.php");
require_once(dirname(__FILE__) . "/IDSReport.class.php");
require_once(dirname(__FILE__) . "/IDSEvent.class.php");
require_once(dirname(__FILE__) . "/IDSConverter.class.php");

/**
 * Helper function to trigger E_USER_ERROR
 *
 * This helper function replaces the PHP5 exception mechanism
 *
 * @author Stefan Gehrig (gehrig@ishd.de)
 * @package PHP4IDS
 * @version	0.1
 * @param sring $message
 */
function error($message)
{
	trigger_error($message, E_USER_ERROR);
}

/**
 * Introdusion Dectection System
 *
 * This class provides function(s) to scan incoming data for
 * malicious script fragments
 *
 * @author Stefan Gehrig (gehrig@ishd.de)
 * @package PHP4IDS
 * @version	0.2
 */
class IDSMonitor
{
	/**
	 * Assembled tags
	 *
	 * @access private
	 * @var array
	 */
	var $mTags=null;
	/**
	 * Request Data to scan for malicious script fragments
	 *
	 * @access private
	 * @var array
	 */
	var $mRequest=null;
	/**
	 * Storage provider from where to get filter rules
	 *
	 * @access private
	 * @var IDSStorageProvider
	 */
	var $mStorage=null;
	/**
	 * Request keys not to scan
	 *
	 * This array is meant to define which variables need to be ignored
	 * by the PHP4IDS - default is the utmz google analytics parameter
	 *
	 * @access private
	 * @var array
	 */
	var $mExceptions=array('__utmz');

	/**
	 * Scan request keys for malicious data
	 *
	 * @access private
	 * @var bool
	 */
	var $mScanKeys=false;

	/**
	 * Constructor
	 *
	 * @access public
	 * @param array $request Request array
	 * @param IDSStorageProvider $storage Filter storage object
	 * @param array $tags List of tags where filters should be applied
	 */
	function IDSMonitor($request, &$storage, $tags=null)
	{
		if (!empty($request))
		{
			$this->mStorage=&$storage;
			$this->mRequest=$request;
			$this->mTags=$tags;
		}
	}

	/**
	 * Starts the detection mechanism and returns IDSReport
	 *
	 * @access public
	 * @return	IDSReport
	 */
	function &Run()
	{
		$report=&new IDSReport();
		if(!empty($this->mRequest))
		{
			foreach ($this->mRequest as $key => $value)
			{
				$this->Iterate($key, $value, $report);
			}
		}
		return $report;
	}

	/**
	 * Iterates through given data and delegates it
	 * to IDSMonitor::Detect() in order to check for malicious
	 * appearing fragments
	 *
	 * @access private
	 * @param mixed $key
	 * @param mixed $value
	 * @param IDSReport $report
	 */
	function Iterate($key, $value, &$report)
	{
		if (!is_array($value))
		{
			if (is_string($value))
			{
				$filters=$this->Detect($key, $value);
        		if (!is_null($filters))
        		{
	        		$event=&new IDSEvent($key, $value, $filters);
        			$report->AddEvent($event);
				}
			}
		}
		else
		{
			foreach ($value as $subKey => $subValue)
			{
				$this->Iterate($key . '.' . $subKey, $subValue, $report);
			}
		}
	}

	/**
	 * Checks whether given value matches any of the supplied
	 * filter patterns
	 *
	 * @access	private
	 * @param mixed $key
	 * @param mixed $value
	 * @return	array
	 */
	function Detect($key, $value)
	{
        if (preg_match('/[^\w\s\/]+/ims', $value) && !empty($value))
        {
			if (in_array($key, $this->mExceptions, true)) return null;

			$value=IDSConverter::Convert($value);
			$value=get_magic_quotes_gpc() ? stripslashes($value) : $value;
			$key=($this->mScanKeys) ? IDSConverter::Convert($key) : $key;

			$filters=array();
			$filterSet=$this->mStorage->FilterSet();
			for ($i=0; $i<count($filterSet); $i++)
			{
				$filter=&$filterSet[$i];
				if (is_array($this->mTags))
				{
					if (array_intersect($this->mTags, $filter->Tags()))
					{
						if ($this->Match($key, $value, $filter)) $filters[]=&$filter;
					}
				}
				else
				{
					if ($this->Match($key, $value, $filter)) $filters[]=&$filter;
				}
			}
			return empty($filters) ? null : $filters;
		}
	}

	/**
	 * Matches given value and/or key against given filter
	 *
	 * @access private
	 * @param	string $value
	 * @param	IDSFilter $filter
	 * @return	bool
	 */
	function Match($key, $value, &$filter)
	{
		if ($this->mScanKeys)
		{
			if ($filter->Match($key)) return true;
		}
		return $filter->Match($value);
	}

	/**
	 * Sets exception array
	 *
	 * @access public
	 * @param mixed $exceptions
	 */
	function SetExceptions($exceptions)
	{
		if (!is_array($exceptions)) $exceptions=array($exceptions);
		$this->mExceptions=$exceptions;
	}

	/**
	 * Returns exception array
	 *
	 * @access public
	 * @return	array
	 */
	function Exceptions() { return $this->mExceptions; }

	/**
	 * Sets boolean value for scan keys
	 *
	 * @access public
	 * @param bool $exceptions
	 */
	function SetScanKeys($scanKeys)
	{
		if (!is_bool($scanKeys)) error('Expected $scanKeys to be a boolen, ' . gettype($scanKeys) . ' given');
		$this->mScanKeys=$scanKeys;
	}

	/**
	 * Returns value for scan keys
	 *
	 * @access public
	 * @return	bool
	 */
	function ScanKeys() { return $this->mScanKeys; }
}
?>