<?php
namespace jc\ui\xhtml\parsers\node ;

use jc\ui\xhtml\Text;

class AttributeValue extends Text
{
	public function __construct($sQuoteType,$nPosition,$nLine)
	{
		parent::__construct($nPosition,0,$nLine,'') ;
		
		$this->sQuoteType = $sQuoteType ;
	}
	
	public function quoteType()
	{
		return $this->sQuoteType ;
	} 
	
	private $sQuoteType ;
}

?>