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
 * @subpackage Storage
 */

require_once(dirname(__FILE__) . "/IDSFilter.class.php");

/**
 * Abstract Filter Storage
 *
 * Class to assure the systems API
 *
 * @author Stefan Gehrig (gehrig@ishd.de)
 * @package PHP4IDS
 * @subpackage Storage
 * @abstract
 * @version	0.1
 */
class IDSStorageProvider
{
	/**
	* Constructor
	*
	* @access	public
	* @return	void
	*/
	function IDSStorageProvider() { }

	/**
	* Returns array containing all filters
	*
	* @abstract
	* @access	public
	* @return	array
	*/
	function FilterSet()
	{
		error("This method needs to be overridden in subclasses");
	}
}

/**
 * Simple storage class using PHP array
 *
 * This class provides filter patterns stored in a PHP array.
 *
 * @author Stefan Gehrig (gehrig@ishd.de)
 * @package PHP4IDS
 * @subpackage Storage
 * @version	0.1
 */
class IDSSimpleStorageProvider extends IDSStorageProvider
{
	/**
	 * Filterset
	 *
	 * @access 	private
	 * @var 	mixed
	 */
	var $mFilterSet=array();

	/**
	 * Constructor
	 *
	 * @access	public
	 * @param array $filterSet
	 * @return	void
	 */
	function IDSSimpleStorageProvider($filterSet=null)
	{
		parent::IDSStorageProvider();
		if (!is_null($filterSet)) $this->SetFilterSet($filterSet);
	}

	/**
	 * Add filters to filter storage array
	 *
	 * @access	public
	 * @param array $filterSet
	 */
	function SetFilterSet($filterSet)
	{
		for ($i=0; $i<count($filterSet); $i++)
		{
			$this->AddFilter($filterSet[$i]);
		}
	}

	/**
	* Returns array containing all filters
	*
	* @access	public
	* @return	array
	*/
	function FilterSet() { return $this->mFilterSet; }

	/**
	 * Add a single IDSFilter
	 *
	 * @access	public
	 * @param IDSFilter $filter
	 */
	function AddFilter(&$filter)
	{
		if (!is_a($filter, "IDSFilter")) error("Invalid parameter type");
		$this->mFilterSet[]=&$filter;
	}
}

/**
 * Filter Storage Class
 *
 * This class provides filter patterns stored in a xml file
 * using DOM XML (available only in PHP4)
 *
 * @author Stefan Gehrig (gehrig@ishd.de)
 * @package PHP4IDS
 * @subpackage Storage
 * @version	0.1
 */
class IDSXmlStorageProvider extends IDSStorageProvider
{
	/**
	 * Either filname to xml filter storage file
	 * or xml storage file as string.
	 *
	 * @access 	private
	 * @var 	string
	 */
	var $mXmlFile=null;

	var $mFilterSet=null;

	/**
	 * Constructor
	 *
	 * @access	public
	 * @param string $xmlFile
	 * @return	void
	 */
	function IDSXmlStorageProvider($xmlFile)
	{
		parent::IDSStorageProvider();
		$this->mXmlFile=$xmlFile;
	}

	/**
	* Returns array containing all filters
	*
	* @access	public
	* @return	array
	*/
	function FilterSet()
	{
		if (is_null($this->mFilterSet)) $this->mFilterSet=$this->LoadFilterSet();
		return $this->mFilterSet;
	}


	/**
	 * Load filters from xml (either file or xml string)
	 *
	 * @access private
	 * @return array
	 */
	function LoadFilterSet()
	{
		if (is_file($this->mXmlFile))
		{
			if (!is_readable($this->mXmlFile))
				error("FilterSet file " . basename($this->mXmlFile) . " not readable");
			else $dom=domxml_open_file($this->mXmlFile);
		}
		else $dom=domxml_open_mem($this->mXmlFile);
		if (!$dom) error("Error while reading filterset data");

		$ctx=&$dom->xpath_new_context();
		$xpresult=&$ctx->xpath_eval("/filters/filter");

		$ret=array();
		foreach ($xpresult->nodeset as $xmlFilter)
		{
			$rule=null;
			$description=null;
			$tags=array();
			$impact=null;

			foreach ($xmlFilter->child_nodes() as $child)
			{
				if ($child->node_type()!==XML_ELEMENT_NODE) continue;

				if ($child->node_name()==="rule") $rule=$child->get_content();
				else if ($child->node_name()==="description") $description=$child->get_content();
				else if ($child->node_name()==="impact") $impact=intval($child->get_content());
				else if ($child->node_name()==="tags")
				{
					foreach ($child->child_nodes() as $tagChild)
					{
						if ($tagChild->node_type()!==XML_ELEMENT_NODE) continue;
						if ($tagChild->node_name()==="tag") array_push($tags, $tagChild->get_content());
					}
				}
			}
			$filter=&new IDSRegexpFilter($rule, $description, $tags, $impact);
			$ret[]=&$filter;
		}
		return $ret;
	}
}
?>