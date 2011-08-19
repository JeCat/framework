<?php
namespace jc\ui\xhtml\compiler ;

use jc\ui\xhtml\Tag;
use jc\ui\xhtml\Node;
use jc\lang\Assert;
use jc\io\IOutputStream;
use jc\ui\CompilerManager;
use jc\ui\IObject;

class NodeCompiler extends BaseCompiler
{
	public function compile(IObject $aObject,IOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Assert::type("jc\\ui\\xhtml\\Node",$aObject,'aObject') ;

		if( $aCompiler=$this->subCompiler(strtolower($aObject->tagName())) )
		{
			$aCompiler->compile($aObject,$aDev,$aCompilerManager) ;
		}
		
		else 
		{
			$this->compileTag($aObject->headTag(), $aDev, $aCompilerManager) ;
			
			if( $aTailTag = $aObject->tailTag() )
			{
				$this->compileChildren($aObject, $aDev, $aCompilerManager) ;
				
				$this->compileTag($aTailTag, $aDev, $aCompilerManager) ;
			}
		}
	}

	protected function compileTag(Tag $aTag,IOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		$this->outputTargetCode('<') ;
		if( $aTag->isTail() )
		{
			$this->outputTargetCode('/') ;
		}
		
		$this->outputTargetCode($aTag->name()) ;
		
		// 属性
		$aAttrs = $aTag->attributes() ;
		foreach ($aAttrs->valueIterator() as $aAttrVal)
		{
			$this->outputTargetCode(' ') ;
			
			// 具名属性
			if($sName=$aAttrVal->name())
			{
				$this->outputTargetCode($sName) ;
				$this->outputTargetCode('=') ;
			}
			
			$this->outputTargetCode($aAttrVal->quoteType()) ;
			if( $aAttrCompiler = $aCompilerManager->compiler($aAttrVal) )
			{
				$this->flushTargetCode($aDev) ;
				$aAttrCompiler->compile($aAttrVal,$aDev,$aCompilerManager) ;
			}
			else 
			{
				if($sName)
				{
					$this->outputTargetCode(
						addslashes($aAttrs->get($sName))
					) ;
				}
				else 
				{
					$this->outputTargetCode(
						addslashes($aAttrs->source())
					) ;
				}
			}
		
			$this->outputTargetCode($aAttrVal->quoteType()) ;
		}
		
		if( $aTag->isSingle() )
		{
			$this->outputTargetCode(' /') ;
		}
		
		$this->outputTargetCode('>') ;
		$this->flushTargetCode($aDev) ;
	}
	
	public function compileChildren(Node $aNode,IOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		foreach($aNode->childElementsIterator() as $aObject)
		{
			if( $aCompiler = $aCompilerManager->compiler($aObject) )
			{
				$aCompiler->compile($aObject,$aDev,$aCompilerManager) ;
			}
		}
	}
	
	//
	static public function assignVariableName($sPrefix='')
	{
		return $sPrefix.'var'.self::$nVariableAssigned++ ;
	}
	static private $nVariableAssigned = 0 ;
}

?>