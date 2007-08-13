<?php
class IDSFilter
{
	var $mRule;
	var $mTags=array();
	var $mImpact=0;
	var $mDescription;

	/**
	 * @param	mixed $rule				Filter rule
	 * @param	string $description		Filter description
	 * @param	array $tags				List of tags
	 * @param	int $impact			Filter impact level
	 */
	function IDSFilter($rule, $description, $tags, $impact)
	{
		$this->mRule=$rule;
		$this->mTags=$tags;
		$this->mImpact=$impact;
		$this->mDescription=$description;
	}

	/**
	 * @return	bool
	 */
	function Match($string)
	{
		error("This method needs to be overridden in subclasses");
	}

	function Description() { return $this->mDescription; }
	function Tags() { return $this->mTags; }
	function Rule() { return $this->mRule; }
	function Impact() { return $this->mImpact; }
}

class IDSRegexpFilter extends IDSFilter
{
	function Match($string)
	{
		if (!is_string($string)) error('Invalid argument. Exptected a string, got ' . gettype($string));
		return (bool) preg_match('/' . $this->Rule() . '/iDs', $string);
	}
}
?>