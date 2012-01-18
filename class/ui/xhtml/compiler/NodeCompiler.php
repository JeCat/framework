<?php
namespace org\jecat\framework\ui\xhtml\compiler ;

use org\jecat\framework\ui\xhtml\Tag;
use org\jecat\framework\ui\xhtml\Node;
use org\jecat\framework\lang\Assert;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\IObject;

class NodeCompiler extends BaseCompiler
{
	public function compile(IObject $aObject,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Assert::type("org\\jecat\\framework\\ui\\xhtml\\Node",$aObject,'aObject') ;

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
				get_class($aAttrCompiler) ;
			}
			else 
			{
				if($sName)
				{
					$aDev->output(
						addcslashes($aAttrs->get($sName),$aAttrVal->quoteType().'\\')
					) ;
				}
				else 
				{
					$aDev->output(
						addcslashes($aAttrs->source(),$aAttrVal->quoteType().'\\')
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
/**
 * @wiki /模板/标签/自定义标签
 *
 *  xxxxxx
 *
 */
?>