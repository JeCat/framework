<?php
namespace jc\ui\xhtml\compiler\node;

use jc\lang\Exception;

use jc\ui\xhtml\AttributeValue;
use jc\ui\TargetCodeOutputStream;
use jc\lang\Type;
use jc\ui\xhtml\compiler\ExpressionCompiler;
use jc\ui\CompilerManager;
use jc\ui\IObject;
use jc\ui\xhtml\compiler\NodeCompiler;

class SubTemplateDefineCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Type::check ( "jc\\ui\\xhtml\\Node", $aObject ) ;
		
		$aAttributes = $aObject->attributes() ;	
		if( $aAttributes->has("name") )
		{
			$sSubTemplateName = $aAttributes->string("name") ;		
		}
		else 
		{
			if( !$aNameVal = $aAttributes->anonymous() )
			{
				throw new Exception("subtemplate:define 节点缺少name属性(line:%d)",$aObject->line()) ;
			}
			$sSubTemplateName = $aNameVal->source() ;
		}
		
		if( !is_callable($sSubTemplateName,true) )
		{
			throw new Exception("subtemplate:define 节点的name属性使用了无效的字符：%d",$sSubTemplateName) ;
		}
		
		$aDev->write("\r\n\r\n// -- subtemplate start ----------------------") ;
		$aDev->write("function __subtemplate_{$sSubTemplateName}(\$aVariables,\$aDevice){ ") ;
		
		$this->compileChildren($aObject,$aDev,$aCompilerManager) ;
		
		$aDev->write("}// -- subtemplate end ----------------------\r\n\r\n") ;
	}

}

 ?>