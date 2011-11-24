<?php
namespace org\jecat\framework\ui\xhtml ;


class AttributeValue extends Text
{
	public function __construct($sName,$sQuoteType,$nPosition,$nLine)
	{
		parent::__construct($nPosition,0,$nLine,'') ;
		
		$this->sQuoteType = $sQuoteType ;
		$this->sName = $sName ;
	}
	
	public function name()
	{
		return $this->sName ;
	}
	
	public function setName($sName)
	{
		$this->sName = $sName ;
	}
	
	public function quoteType()
	{
		return $this->sQuoteType ;
	} 
	
	private $sQuoteType ;
	
	private $sName ;
}

?>