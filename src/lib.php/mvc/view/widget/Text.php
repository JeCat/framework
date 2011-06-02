<?php
namespace jc\mvc\view\widget ;

class Text extends FormWidget
{
	public function __construct($sId,$bMultiLine=false,$aView=null)
	{
		$sTemplate = $bMultiLine? 'ViewWidgetMultiLineText.template.html': 'ViewWidgetSingleLineText.template.html' ;
		$this->bMultiLine = $bMultiLine ;
			
		parent::__construct($sId,$sTemplate,$aView) ;
	}
	
	public function isMultiLine()
	{
		return $this->bMultiLine ;
	}
	
	private $bMultiLine ;
	
}

?>