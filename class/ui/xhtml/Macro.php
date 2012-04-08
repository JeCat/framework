<?php
namespace org\jecat\framework\ui\xhtml ;

class Macro extends ObjectBase
{
	public function __construct($sMacroType,$nPosition,$nEndPosition,$nLine,$sSource)
	{
		ObjectBase::__construct($nPosition,$nEndPosition,$nLine,$sSource) ;
		
		$this->sMacroType = $sMacroType ;
	}
	
	public function setBorder($sStartMacro,$sEndMacro)
	{
		$this->sStartMacro = $sStartMacro ;
		$this->sEndMacro = $sEndMacro ;
	}
	
	public function borderStartMacro()
	{
		return $this->sStartMacro ;
	}
	
	public function borderEndMacro()
	{
		return $this->sEndMacro ;
	}
	
	public function macroType()
	{
		return $this->sMacroType ;
	}
	
	private $sMacroType ;
	private $sStartMacro ;
	private $sEndMacro ;	
	
}

?>