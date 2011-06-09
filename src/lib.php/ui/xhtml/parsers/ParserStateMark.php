<?php
namespace jc\ui\xhtml\parsers ;

use jc\lang\Exception;
use jc\lang\Assert;
use jc\ui\xhtml\ObjectBase;
use jc\ui\xhtml\Mark;
use jc\ui\xhtml\IObject;
use jc\util\String;

class ParserStateMark extends ParserState
{
	public function __construct()
	{
		parent::__construct() ;
		self::setSingleton($this) ;
	}
	
	public function active(IObject $aParent,String $aSource,$nPosition)
	{
		$sStartMark = $this->determineMarkBorder($aSource, $nPosition) ;
		
		$aMark = new Mark($aSource->byte($nPosition+strlen($sStartMark)),$nPosition, 0, ObjectBase::getLine($aSource,$nPosition), '') ;
		$aMark->setBorder($sStartMark,$this->arrMarkBorder[$sStartMark]) ;
		$aParent->add($aMark) ;
		
		return $aMark ;
	}
	
	public function examineEnd(String $aSource, &$nPosition,IObject $aObject) 
	{
		Assert::type("jc\\ui\\xhtml\\Mark", $aObject) ;
		
		$sEndMark = $aObject->borderEndMark() ;
		$nBorderWidth = strlen($sEndMark) ;
		
		if($aSource->substr($nPosition,$nBorderWidth)==$sEndMark)
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
		Assert::type("jc\\ui\\xhtml\\Mark", $aObject, 'aObject') ;
				
		$sTextPos = $aObject->position() + strlen($aObject->borderStartMark()) + 1 ;
		$sTextLen = ($nPosition-strlen($aObject->borderEndMark())) - $sTextPos + 1 ;
		$sText = $aSource->substr( $sTextPos, $sTextLen ) ;
		
		$aObject->setEndPosition($nPosition) ;
		$aObject->setSource($sText) ;
		
		return $aObject->parent() ;
	}
	
	public function examineStart(String $aSource, &$nPosition,IObject $aObject)
	{
		return $this->determineMarkBorder($aSource,$nPosition)? true: false ;
	}
	
	private function determineMarkBorder(String $aSource, $nPosition)
	{
		foreach($this->arrMarkBorder as $sStartMark=>$sEndMark)
		{
			$nBorderWidth = strlen($sStartMark) ;
			
			if( $aSource->substr($nPosition,$nBorderWidth)==$sStartMark 
					and in_array($aSource->byte($nPosition+$nBorderWidth),array('?','=','*')) )
			{
				return $sStartMark ;
			}
		}
		
		return null ;
	}
	
	public function addMarkBorder($sStartMark,$sEndMark)
	{
		$this->arrMarkBorder[$sStartMark] = $sEndMark ;
	}
	
	private $arrMarkBorder = array(
		'{'=>'}' ,
		'{#'=>'#}' ,
	) ;
}

?>