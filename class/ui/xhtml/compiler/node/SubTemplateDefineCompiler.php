<?php
namespace org\jecat\framework\ui\xhtml\compiler\node;

use org\jecat\framework\lang\Exception;

use org\jecat\framework\ui\xhtml\AttributeValue;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\lang\Type;
use org\jecat\framework\ui\xhtml\compiler\ExpressionCompiler;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\xhtml\compiler\NodeCompiler;
use org\jecat\framework\ui\ObjectContainer;

class SubTemplateDefineCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Type::check ( "org\\jecat\\framework\\ui\\xhtml\\Node", $aObject ) ;
		
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
		
		$this->compileChildren($aObject,$aObjectContainer,$aDev,$aCompilerManager) ;
		
		$aDev->write("}// -- subtemplate end ----------------------\r\n\r\n") ;
	}

}

 ?>