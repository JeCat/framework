<?php
namespace jc\mvc\view\widget ;

class Text extends FormWidget
{
	public function __construct($sId,$bMultiLine=false,$aView=null)
	{
		//$sTemplate = $bMultiLine? 'ViewWidgetText.template.html': 'ViewWidgetSingleLineText.template.html' ;
		$this->bMultiLine = $bMultiLine ;
			
		parent::__construct($sId,'ViewWidgetText.template.html',$aView) ;
	}
	
	public function isMultiLine()
	{
		return $this->bMultiLine ;
	}
	
	private $bMultiLine ;
	
}

?>