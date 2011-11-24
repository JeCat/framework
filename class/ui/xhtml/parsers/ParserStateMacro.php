<?php
namespace org\jecat\framework\ui\xhtml\parsers ;

use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\Assert;
use org\jecat\framework\ui\xhtml\ObjectBase;
use org\jecat\framework\ui\xhtml\Macro;
use org\jecat\framework\ui\xhtml\IObject;
use org\jecat\framework\util\String;

class ParserStateMacro extends ParserState
{
	public function __construct()
	{
		parent::__construct() ;
		self::setSingleton($this) ;
	}
	
	public function active(IObject $aParent,String $aSource,$nPosition)
	{
		$sStartMacro = $this->determineMacroBorder($aSource, $nPosition) ;
		
		$aMacro = new Macro($aSource->byte($nPosition+strlen($sStartMacro)),$nPosition, 0, ObjectBase::getLine($aSource,$nPosition), '') ;
		$aMacro->setBorder($sStartMacro,$this->arrMacroBorder[$sStartMacro]) ;
		$aParent->add($aMacro) ;
		
		return $aMacro ;
	}
	
	public function examineEnd(String $aSource, &$nPosition,IObject $aObject) 
	{
		Assert::type("org\\jecat\\framework\\ui\\xhtml\\Macro", $aObject) ;
		
		$sEndMacro = $aObject->borderEndMacro() ;
		$nBorderWidth = strlen($sEndMacro) ;
		
		if($aSource->substr($nPosition,$nBorderWidth)==$sEndMacro)
		{
			$nPosition+= $nBorderWidth-1 ;
			return true ;
		}
		else 
		{
			return false ;
		}
	}
	
	public function complete(IObject $aObject,String $aSource,$nPosition)
	{
		Assert::type("org\\jecat\\framework\\ui\\xhtml\\Macro", $aObject, 'aObject') ;
				
		$sTextPos = $aObject->position() + strlen($aObject->borderStartMacro()) + 1 ;
		$sTextLen = ($nPosition-strlen($aObject->borderEndMacro())) - $sTextPos + 1 ;
		$sText = $aSource->substr( $sTextPos, $sTextLen ) ;
		
		$aObject->setEndPosition($nPosition) ;
		$aObject->setSource($sText) ;
		
		return $aObject->parent() ;
	}
	
	public function examineStart(String $aSource, &$nPosition,IObject $aObject)
	{
		return $this->determineMacroBorder($aSource,$nPosition)? true: false ;
	}
	
	public function addMacroType($sType)
	{
		if(!in_array($sType,$this->arrMacroTypes))
		{
			$this->arrMacroTypes[] = $sType ;
		}
		
		return $this ;
	}
	
	private function determineMacroBorder(String $aSource, $nPosition)
	{
		foreach($this->arrMacroBorder as $sStartMacro=>$sEndMacro)
		{
			$nBorderWidth = strlen($sStartMacro) ;
			
			if( $aSource->substr($nPosition,$nBorderWidth)==$sStartMacro 
					and in_array($aSource->byte($nPosition+$nBorderWidth),$this->arrMacroTypes) )
			{
				return $sStartMacro ;
			}
		}
		
		return null ;
	}
	
	public function addMacroBorder($sStartMacro,$sEndMacro)
	{
		$this->arrMacroBorder[$sStartMacro] = $sEndMacro ;
	}
	
	private $arrMacroBorder = array(
		'{'=>'}' ,
		'{#'=>'#}' ,
	) ;
	
	private $arrMacroTypes = array() ;
}

?>