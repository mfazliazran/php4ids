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

require_once(dirname(__FILE__) . "/IDSEvent.class.php");

/**
 * PHP4IDS report object
 *
 * The report objects collects a number of events in order to present the
 * filter results. It provides a convenient API to work with the results.
 *
 * @author Stefan Gehrig (gehrig@ishd.de)
 * @package PHP4IDS
 * @version	0.2
 */
class IDSReport
{
	/**
	 * List of events
	 *
	 * @access private
	 * @var array
	 */
	var $mEvents=array();
	/**
	 * List of tags
	 *
	 * This list of tags is collected from the collected event objects
	 * on demand (when IDSReport->Tags() is called)
	 *
	 * @access private
	 * @var	array
	 */
	var $mTags=null;
	/**
	 * Impact level
	 *
	 * The impact level is calculated on demand (by adding the results of
	 * the event objects on IDSReport->Impact())
	 *
	 * @access private
	 * @var	integer
	 */
	var $mImpact=null;

	/**
	 * Constructor
	 *
	 * @access public
	 * @param array $events
	 */
	function IDSReport($events=null)
	{
		if (!is_null($events))
		{
			for ($i=0; $i<count($events); $i++)
			{
				$this->AddEvent($events[$i]);
			}
		}
	}

	/**
	 * Add an IDS_Event object to the report
	 *
	 * @access public
	 * @param IDS_Event $event
	 * @return IDSReport
	 */
	function &AddEvent(&$event)
	{
		if (!is_a($event, "IDSEvent")) error("Invalid parameter type");
		$this->Clear();
		$this->mEvents[$event->Name()]=&$event;
		return $this;
	}

	/**
	 * Get event (by name)
	 *
	 * Every event is named by its source name. You can get a specific event by
	 * its name with this method.
	 *
	 * @access public
	 * @param	scalar $name
	 * @return	IDSEvent
	 */
	function &GetEvent($name)
	{
		if (!is_scalar($name)) error('Invalid argument type given');
		if ($this->HasEvent($name)) return $this->mEvents[$name];
		return null;
	}

	/**
	 * Get list of tags
	 *
	 * Returns a list of collected tags from all of the IDSEvent sub-objects
	 *
	 * @access public
	 * @return	array
	 */
	function Tags()
	{
		if (is_null($this->mTags))
		{
			$this->mTags=array();
			$eventNames=array_keys($this->mEvents);
			for ($i=0; $i<count($eventNames); $i++)
			{
				$event=&$this->mEvents[$eventNames[$i]];
				$this->mTags=array_merge($this->mTags, $event->Tags());
			}
			$this->mTags=array_values(array_unique($this->mTags));
		}
		return $this->mTags;
	}

	/**
	 * Get impact level
	 *
	 * Return calculated impact level. Every IDSEvent sub object and
	 * its IDSFilter objects are used to calculate the overall impact
	 * level.
	 *
	 * @access public
	 * @return	integer
	 */
	function Impact()
	{
		if (is_null($this->mImpact))
		{
			$this->mImpact=0;
			$eventNames=array_keys($this->mEvents);
			for ($i=0; $i<count($eventNames); $i++)
			{
				$event=&$this->mEvents[$eventNames[$i]];
				$this->mImpact+=$event->Impact();
			}
		}
		return $this->mImpact;
	}

	/**
	 * Event with name $name is existant?
	 *
	 * @access public
	 * @var	scalar $name
	 */
	function HasEvent($name)
	{
		if (!is_scalar($name)) error('Invalid argument given');
		return array_key_exists($name, $this->mEvents);
	}

	/**
	 * Number of events
	 *
	 * Returns the number of events contained in the IDSReport object.
	 *
	 * @access public
	 * @return	integer
	 */
	function Count() { return count($this->mEvents); }

	/**
	 * Checks whether or not report is filled
	 *
	 * @access public
	 * @return	bool
	 */
	function IsEmpty() { return empty($this->mEvents); }

	/**
	 * Clear calculated/collected values
	 *
	 * @access public
	 * @return	void
	 */
	function Clear()
	{
		$this->mImpact=null;
		$this->mTags=null;
	}
}
?>