<?php
require_once(dirname(__FILE__) . "/IDSFilter.class.php");

class IDSEvent
{
	var $mName=null;
	var $mValue=null;
	var $mFilters=array();
	var $mImpact=null;
	var $mTags=null;

	/**
	 * @param scalar $name
	 * @param scalar $value
	 * @param array $filters
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
	 * @return scalar
	 */
	function Name() { return $this->mName; }

	/**
	 * @return scalar
	 */
	function Value() { return $this->mValue; }

	/**
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
	 * @return array
	 */
	function Filters() { return $this->mFilters; }

	/**
	 * @return integer
	 */
	function Count() { return count($this->mFilters); }
}
?>