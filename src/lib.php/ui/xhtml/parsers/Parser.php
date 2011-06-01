<?php
namespace jc\ui\xhtml\parsers ;

use jc\ui\IObject as IUiObject;
use jc\util\String;
use jc\ui\IInterpreter;
use jc\util\Stack ;
use jc\ui\xhtml\ObjectBase ;
use jc\lang\Object as JcObject ;

/**
 * @author alee
 */
class Parser extends JcObject implements IInterpreter
{
	/**
	 * return IObject
	 */
	public function parse(String $aSource,IUiObject $aObjectContainer,$sSourcePath)
	{
		$nProcIndex = 0 ;
		
		$aState = ParserStateDefault::singleton() ;
		$aCurrentObject = $aRootObject = new ObjectBase(0,$aSource->length()-1,0,'') ;
		
		while( $nProcIndex < $aSource->length() )
		{
			$aState = ParserState::queryState($aCurrentObject) ;
			
			$aNewState = $aState->examineStateChange($aSource,$nProcIndex,$aCurrentObject) ;
			if( $aNewState )
			{
				// 切换状态
				if( $aNewState!=$aState )
				{
					// 
					$aCurrentObject = $aState->sleep($aCurrentObject,$aSource,$nProcIndex-1) ;
					
					$aCurrentObject = $aNewState->active($aCurrentObject,$aSource,$nProcIndex) ;
				}
			}
			
			else 
			{
				$aCurrentObject = $aState->complete($aCurrentObject,$aSource,$nProcIndex) ;
				if(!$aCurrentObject)
				{
					break ;
				}
				
				$aCurrentObject = ParserState::queryState($aCurrentObject)->wakeup($aCurrentObject,$aSource,$nProcIndex) ;
			}
			
			$nProcIndex ++ ;
		}
		
		$aObjectContainer->clear() ;
		foreach($aRootObject->iterator() as $aObject)
		{
			$aObjectContainer->add($aObject) ;
		}
	}
	
}

?>