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
	
	static public function createInstance($sName,$sValue,$sQuoteType='"',$nPosition=0,$nLine=0)
	{
		$aVal = new self($sName,$sQuoteType,$nPosition,$nLine) ;
		$aVal->setSource($sValue) ;
		return $aVal ;
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
	
	public function setQuoteType($sQuoteType)
	{
		$this->sQuoteType = $sQuoteType ;
	} 
	
	private $sQuoteType ;
	
	private $sName ;
}

?>