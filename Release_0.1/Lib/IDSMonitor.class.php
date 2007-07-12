<?php
require_once(dirname(__FILE__) . "/IDSStorageProvider.class.php");
require_once(dirname(__FILE__) . "/IDSReport.class.php");
require_once(dirname(__FILE__) . "/IDSEvent.class.php");
require_once(dirname(__FILE__) . "/IDSConverter.class.php");

function error($message)
{
	trigger_error($message, E_USER_ERROR);
}

class IDSMonitor
{
	/**
	 * @var array
	 */
	var $mTags=null;
	/**
	 * @var array
	 */
	var $mRequest=null;
	/**
	 * @var IDSStorageProvider
	 */
	var $mStorage=null;
	/**
	 * @var array
	 */
	var $mExceptions=array('__utmz');

	/**
	 * @param array $request
	 * @param IDSStorageProvider $storage
	 * @param array $tags
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
	 * @param mixed $key
	 * @param mixed $value
	 * @param IDSReport $report
	 */
	function Iterate($key, $value, &$report)
	{
		if (!is_array($value))
		{
			$filters=$this->Detect($key, $value);
        	if (!is_null($filters))
        	{
        		$event=&new IDSEvent($key, $value, $filters);
        		$report->AddEvent($event);
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
	 * @param mixed $key
	 * @param mixed $value
	 * @return	array
	 */
	function Detect($key, $value)
	{
        if (preg_match('/\W+/iDs', $value) && !empty($value))
        {
			if (in_array($key, $this->mExceptions, true)) return null;
			$filters=array();
			$filterSet=$this->mStorage->FilterSet();
			for ($i=0; $i<count($filterSet); $i++)
			{
				$filter=&$filterSet[$i];
				if (is_array($this->mTags))
				{
					if (array_intersect($this->mTags, $filter->Tags()))
					{
						if ($this->PrepareMatching($key, $value, $filter)) $filters[]=&$filter;
					}
				}
				else
				{
					if ($this->PrepareMatching($key, $value, $filter)) $filters[]=&$filter;
				}
			}
			return empty($filters) ? null : $filters;
		}
	}

	/**
	 * @param	string $value
	 * @param	IDSFilter $filter
	 * @return	bool
	 */
	function PrepareMatching($key, $value, &$filter)
	{
        $value=IDSConverter::ConvertFromUTF7($value);
        $value=IDSConverter::ConvertFromJSCharcode($value);
		return $filter->Match($value);
	}

	/**
	 * @param mixed $exceptions
	 */
	function SetExceptions($exceptions)
	{
		if (!is_array($exceptions)) $exceptions=array($exceptions);
		$this->mExceptions=$exceptions;
	}

	/**
	 * @return	array
	 */
	function Exceptions() { return $this->mExceptions; }
}
?>