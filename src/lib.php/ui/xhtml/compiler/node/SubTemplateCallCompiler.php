<?php
namespace jc\ui\xhtml\compiler\node;

use jc\lang\Exception;

use jc\ui\xhtml\AttributeValue;
use jc\io\IOutputStream;
use jc\lang\Type;
use jc\ui\xhtml\compiler\ExpressionCompiler;
use jc\ui\CompilerManager;
use jc\ui\IObject;
use jc\ui\xhtml\compiler\NodeCompiler;

class SubTemplateCallCompiler extends NodeCompiler
{
	public function compile(IObject $aObject, IOutputStream $aDev, CompilerManager $aCompilerManager)
	{
		Type::check ( "jc\\ui\\xhtml\\Node", $aObject );

		$aAttributes = $aObject->attributes() ;
	
		if( $aAttributes->has("name") )
		{
			$sSubTemplateName = $aAttributes->get("name") ;		
		}
		else 
		{
			if( !$aSubTemplateNameVal = $aAttributes->anonymous() )
			{
				throw new Exception("subtemplate:define 节点缺少name属性(line:%d)",$aObject->line()) ;
			}
			$sSubTemplateName = '"' . addslashes($aSubTemplateNameVal->source()) . '"' ;
		}
		
		$aDev->write("<?php \r\n") ;
		
		// 是否继承父模板中的变量
		$bExtendParentVars = $aAttributes->has("vars")? $aAttributes->bool('vars'): false ;
	
		// variables
		if(!$bExtendParentVars)
		{
			$aDev->write("\$__subtemplate_aVariables = new \\jc\\util\\HashTable() ; \r\n");
		}
		else
		{
			$aDev->write("\$__subtemplate_aVariables = \$aVariables ; \r\n");
		}
		
		// other variables
		foreach($aAttributes as $sName=>$aValue)
		{
			if( substr($sName,0,4)=='var.' and $sVarName=substr($sName,4) )
			{
				$sVarName = '"'. addslashes($sVarName) . '"' ;
				$sValue = ExpressionCompiler::compileExpression($aValue->source()) ;
				$aDev->write("\$__subtemplate_aVariables->set({$sVarName},{$sValue}) ; \r\n");
			}
		}
		
		$aDev->write("call_user_func_array(\$aVariables->get('__subtemplate_'.{$sSubTemplateName}),array(\$__subtemplate_aVariables,\$aDevice)) ;?>\r\n") ;
	}

}

?>