<?php
namespace jc\ui\xhtml ;

class Mark extends ObjectBase
{
	public function __construct($sMarkType,$nPosition,$nEndPosition,$nLine,$sSource)
	{
		ObjectBase::__construct($nPosition,$nEndPosition,$nLine,$sSource) ;
		
		$this->sMarkType = $sMarkType ;
	}
	
	public function markType()
	{
		return $this->sMarkType ;
	}
	
	private $sMarkType ;
}

?>