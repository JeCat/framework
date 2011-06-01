<?php
namespace jc\ui\xhtml\parsers ;

use jc\lang\Exception;

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
		
		// 未完成的对象
		if( $aCurrentObject!=$aRootObject )
		{
			throw new Exception("分析UI模板时遇到未完成的对象(对象类型：%s，位置：%d行)",array(
					get_class($aCurrentObject), $aCurrentObject->line()
			)) ;
		}
		
		
		$aObjectContainer->clear() ;
		foreach($aRootObject->iterator() as $aObject)
		{
			$aObjectContainer->add($aObject) ;
		}
	}
	
}

?>