<?php
namespace org\jecat\framework\ui\xhtml\compiler\node;

use org\jecat\framework\io\OutputStreamBuffer;

use org\jecat\framework\ui\VariableDeclares;

use org\jecat\framework\lang\Exception;

use org\jecat\framework\ui\xhtml\AttributeValue;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\lang\Type;
use org\jecat\framework\ui\xhtml\compiler\ExpressionCompiler;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\xhtml\compiler\NodeCompiler;
use org\jecat\framework\ui\ObjectContainer;

/**
 * @wiki /模板引擎/标签
 *
 * {|
 *  !<sutemplatedefine>
 *  !不可单行
 *  !定义一个模版
 *  |---
 *  !属性
 *  !
 *  !类型
 *  !默认值
 *  !说明
 *  |---
 *  |name
 *  |必须
 *  |expression
 *  |
 *  |
 *  |}
 */
/**
 * @example /模板引擎/标签/自定义标签:name[1]
 *
 *  通过sutemplatedefine标签编译器的代码演示如何编写一个标签编译器
 */

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
		
		// 准备 VariableDeclares
		$aOldVars = $aObjectContainer->variableDeclares() ;
		$aDeclareVariables = new VariableDeclares() ;
		$aObjectContainer->setVariableDeclares($aDeclareVariables) ;
		$aBuff = new OutputStreamBuffer() ;
		$aDev->write($aBuff) ;
		
		// 编译子对像
		$this->compileChildren($aObject,$aObjectContainer,$aDev,$aCompilerManager) ;
		
		// 声明用到的变量
		$aDeclareVariables->make($aBuff) ;
		$aObjectContainer->setVariableDeclares($aOldVars) ;
		
		
		
		$aDev->write("}// -- subtemplate end ----------------------\r\n\r\n") ;
	}

}

 ?>