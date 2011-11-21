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

class SubTemplateCallCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Type::check ( "jc\\ui\\xhtml\\Node", $aObject );

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
			$aDev->write("\$__subtemplate_aVariables = new \\jc\\util\\DataSrc() ;");
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
				$sValue = ExpressionCompiler::compileExpression($aValue->source()) ;
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

