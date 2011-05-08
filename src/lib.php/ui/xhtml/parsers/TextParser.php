<?php
namespace jc\ui\xhtml\parsers ;

use jc\ui\xhtml\ObjectBase;

use jc\ui\xhtml\Text;

use jc\ui\IObject;
use jc\ui\IInterpreter;
use jc\lang\Object;
use jc\util\String;

class TextParser extends Object implements IInterpreter
{
	public function parse(String $aSource,IObject $aObjectContainer,$sSourcePath)
	{
		$arrObjects = array() ;
		foreach($aObjectContainer->childrenIterator() as $aObject)
		{
			if( ($aObject instanceof ObjectBase) and !($aObject instanceof Text) )
			{
				$arrObjects[$aObject->position()] = $aObject ;
			}
		}
		ksort($arrObjects) ;
		
		
		$arrTexts = array() ;
		$nIdx = 0 ;
		foreach($arrObjects as $aObject)
		{			
			$nLen = $aObject->position()-$nIdx ;
			if( $nLen )
			{
				$sTextSource = Preprocessor::singleton()->quoteDecode(substr($aSource,$nIdx,$nLen)) ;
				$nLine = substr_count($aSource,"\n",0,$nIdx+1) ;
				$nEndPosition = $aObject->position() - 1 ;
				
				$arrTexts[] = new Text($nIdx,$nEndPosition,$nLine,$sTextSource) ;
			}
			
			$nIdx = $aObject->endPosition() + 1 ;
		}
		
		// last pice
		if( $nIdx<$aSource->length()-1 )
		{
			$sTextSource = Preprocessor::singleton()->quoteDecode(substr($aSource,$nIdx)) ;
			$arrTexts[] = new Text($nIdx,$aSource->length()-1,substr_count($aSource,"\n",0,$nIdx+1),$sTextSource) ;
		}
		
		
		foreach ($arrTexts as $aText)
		{
			$aObjectContainer->addChild($aText) ;
		}
	}
}

?>