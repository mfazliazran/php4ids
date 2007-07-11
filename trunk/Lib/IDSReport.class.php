<?php
require_once(dirname(__FILE__) . "/IDSEvent.class.php");

class IDSReport
{
	var $mEvents=array();
	var $mTags=null;
	var $mImpact=null;

	/**
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
	 * @param IDS_Event $event
	 */
	function AddEvent(&$event)
	{
		if (!is_a($event, "IDSEvent")) error("Invalid parameter type");
		$this->Clear();
		$this->mEvents[$event->Name()]=&$event;
	}

	/**
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
	 * @var	scalar $name
	 */
	function HasEvent($name)
	{
		if (!is_scalar($name)) error('Invalid argument given');
		return array_key_exists($name, $this->mEvents);
	}

	/**
	 * @return	integer
	 */
	function Count() { return count($this->mEvents); }

	 /**
	 * @return	bool
	 */
	function IsEmpty() { return empty($this->mEvents); }

	function Clear()
	{
		$this->mImpact=null;
		$this->mTags=null;
	}
}
?>