<?php
namespace org\jecat\framework\ui\xhtml ;

class Mark extends ObjectBase
{
	public function __construct($sMarkType,$nPosition,$nEndPosition,$nLine,$sSource)
	{
		ObjectBase::__construct($nPosition,$nEndPosition,$nLine,$sSource) ;
		
		$this->sMarkType = $sMarkType ;
	}
	
	public function setBorder($sStartMark,$sEndMark)
	{
		$this->sStartMark = $sStartMark ;
		$this->sEndMark = $sEndMark ;
	}
	
	public function borderStartMark()
	{
		return $this->sStartMark ;
	}
	
	public function borderEndMark()
	{
		return $this->sEndMark ;
	}
	
	public function markType()
	{
		return $this->sMarkType ;
	}
	
	private $sMarkType ;
	private $sStartMark ;
	private $sEndMark ;	
	
}

?>