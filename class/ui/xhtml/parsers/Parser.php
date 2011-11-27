<?php
namespace org\jecat\framework\ui\xhtml\parsers ;

use org\jecat\framework\ui\UI;
use org\jecat\framework\fs\IFile;
use org\jecat\framework\io\OutputStreamBuffer;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\ui\ObjectContainer ;
use org\jecat\framework\util\String;
use org\jecat\framework\ui\IInterpreter;
use org\jecat\framework\util\Stack ;
use org\jecat\framework\ui\xhtml\ObjectBase ;
use org\jecat\framework\lang\Object as JcObject ;

/**
 * @author alee
 */
class Parser extends JcObject implements IInterpreter
{
	/**
	 * return IObject
	 */
	public function parse(String $aSource,ObjectContainer $aObjectContainer,UI $aUI)
	{
		$nProcIndex = 0 ;
		
		$aState = ParserStateDefault::singleton() ;
		$aCurrentObject = $aRootObject = new ObjectBase(0,$aSource->length()-1,0,'') ;
		
		while( $nProcIndex < $aSource->length() )
		{
			
			$aNewState = $aState->examineStateChange($aSource,$nProcIndex,$aCurrentObject) ;
			if( $aNewState )
			{
				// 切换状态
				if( $aNewState!=$aState )
				{
					// 
					$aCurrentObject = $aState->sleep($aCurrentObject,$aSource,$nProcIndex-1) ;
					
					$aCurrentObject = $aNewState->active($aCurrentObject,$aSource,$nProcIndex) ;
					
					// 状态变化
					$aState = ParserState::queryState($aCurrentObject) ;
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
				
				// 状态变化
				$aState = ParserState::queryState($aCurrentObject) ;
			}
			
			$nProcIndex ++ ;
		}
		
		// 未完成的对象
		if( $aCurrentObject!=$aRootObject )
		{
			$aBuff = new OutputStreamBuffer() ;
			$aRootObject->printStruct($aBuff) ;
			
			throw new Exception(
				"<pre>\r\n分析UI模板时遇到未完成的对象：%s\r\n%s\r\n</pre>"
				, array( $aCurrentObject->summary(), $aBuff )
			) ;
		}
		
		$aObjectContainer->clear() ;
		foreach($aRootObject->iterator() as $aObject)
		{
			$aObjectContainer->add($aObject) ;
		}
	}
	
	public function compileStrategySignture()
	{
		return __CLASS__ ;
	}
}

?>