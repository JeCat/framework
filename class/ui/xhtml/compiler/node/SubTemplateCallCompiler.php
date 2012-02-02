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

class SubTemplateCallCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Type::check ( "org\\jecat\\framework\\ui\\xhtml\\Node", $aObject );

		$aAttributes = $aObject->attributes() ;
	
		if( $aAttributes->has("name") )
		{
			$sSubTemplateName = $aAttributes->string("name") ;		
		}
		else 
		{
			if( !$aSubTemplateNameVal = $aAttributes->anonymous() )
			{
				throw new Exception("subtemplate:define 节点缺少name属性(line:%d)",$aObject->line()) ;
			}
			$sSubTemplateName = $aSubTemplateNameVal->source() ;
		}
		
		if( !is_callable($sSubTemplateName,true) )
		{
			throw new Exception("subtemplate:define 节点的name属性使用了无效的字符：%d",$sSubTemplateName) ;
		}
		
		$sSubTemplateFuncName = '__subtemplate_' . $sSubTemplateName ;
		
		$aDev->write("\r\n// -- call subtemplate:{$sSubTemplateName} start---------------------") ;
		
		// 是否继承父模板中的变量
		$bExtendParentVars = $aAttributes->has("vars")? $aAttributes->bool('vars'): false ;
	
		// variables
		if(!$bExtendParentVars)
		{
			$aDev->write("\$__subtemplate_aVariables = new \\org\\jecat\\framework\\util\\DataSrc() ;");
			$aDev->write("\$__subtemplate_aVariables->addChild(\$aVariables) ;");
		}
		else
		{
			$aDev->write("\$__subtemplate_aVariables = \$aVariables ;");
		}
		
		// other variables
		foreach($aAttributes as $sName=>$aValue)
		{
			if( substr($sName,0,4)=='var.' and $sVarName=substr($sName,4) )
			{
				$sVarName = '"'. addslashes($sVarName) . '"' ;
				$sValue = ExpressionCompiler::compileExpression($aValue->source(),$aObjectContainer->variableDeclares()) ;
				$aDev->write("\$__subtemplate_aVariables->set({$sVarName},{$sValue}) ;");
			}
		}
		
		
		
		$aDev->write("if( !function_exists('{$sSubTemplateFuncName}') ){") ;
		$aDev->write("\t\$aDevice->write(\"正在调用无效的子模板：{$sSubTemplateName}\") ;") ;
		$aDev->write("} else {") ;
		$aDev->write("\tcall_user_func_array('{$sSubTemplateFuncName}',array(\$__subtemplate_aVariables,\$aDevice)) ;") ;
		$aDev->write("}") ;
		
		$aDev->write("// -- call subtemplate:{$sSubTemplateName} end ---------------------\r\n\r\n") ;
	}

}
?>

