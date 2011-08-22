<?php
namespace jc\ui\xhtml\compiler ;

use jc\ui\xhtml\Tag;
use jc\ui\xhtml\Node;
use jc\lang\Assert;
use jc\ui\TargetCodeOutputStream;
use jc\ui\CompilerManager;
use jc\ui\IObject;

class NodeCompiler extends BaseCompiler
{
	public function compile(IObject $aObject,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
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

	protected function compileTag(Tag $aTag,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		$aDev->output('<') ;
		if( $aTag->isTail() )
		{
			$aDev->output('/') ;
		}
		
		$aDev->output($aTag->name()) ;
		
		// 属性
		$aAttrs = $aTag->attributes() ;
		foreach ($aAttrs->valueIterator() as $aAttrVal)
		{
			$aDev->output(' ') ;
			
			// 具名属性
			if($sName=$aAttrVal->name())
			{
				$aDev->output($sName) ;
				$aDev->output('=') ;
			}
			
			$aDev->output($aAttrVal->quoteType()) ;
			if( $aAttrCompiler = $aCompilerManager->compiler($aAttrVal) )
			{
				$aAttrCompiler->compile($aAttrVal,$aDev,$aCompilerManager) ;
			}
			else 
			{
				if($sName)
				{
					$aDev->output(
						addslashes($aAttrs->get($sName))
					) ;
				}
				else 
				{
					$aDev->output(
						addslashes($aAttrs->source())
					) ;
				}
			}
		
			$aDev->output($aAttrVal->quoteType()) ;
		}
		
		if( $aTag->isSingle() )
		{
			$aDev->output(' /') ;
		}
		
		$aDev->output('>') ;
	}
	
	public function compileChildren(Node $aNode,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
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