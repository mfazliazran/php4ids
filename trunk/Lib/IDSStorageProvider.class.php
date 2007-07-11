<?php
require_once(dirname(__FILE__) . "/IDSFilter.class.php");

class IDSStorageProvider
{
	function IDSStorageProvider() { }

	/**
	 * @return array
	 */
	function FilterSet()
	{
		error("This method needs to be overridden in subclasses");
	}
}

class IDSSimpleStorageProvider extends IDSStorageProvider
{
	var $mFilterSet=array();

	/**
	 * @param array $filterSet
	 */
	function IDSSimpleStorageProvider($filterSet=null)
	{
		parent::IDSStorageProvider();
		if (!is_null($filterSet)) $this->SetFilterSet($filterSet);
	}

	/**
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
	 * @return array
	 */
	function FilterSet() { return $this->mFilterSet; }

	/**
	 * @param IDSFilter $filter
	 */
	function AddFilter(&$filter)
	{
		if (!is_a($filter, "IDSFilter")) error("Invalid parameter type");
		$this->mFilterSet[]=&$filter;
	}
}
class IDSXmlStorageProvider extends IDSStorageProvider
{
	var $mXmlFile=null;

	var $mFilterSet=null;

	/**
	 * @param string $xmlFile
	 */
	function IDSXmlStorageProvider($xmlFile)
	{
		parent::IDSStorageProvider();
		$this->mXmlFile=$xmlFile;
	}

	/**
	 * @return array
	 */
	function FilterSet()
	{
		if (is_null($this->mFilterSet)) $this->mFilterSet=$this->LoadFilterSet();
		return $this->mFilterSet;
	}

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