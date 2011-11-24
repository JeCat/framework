<?php
namespace org\jecat\framework\ui\xhtml\compiler\node ;

use org\jecat\framework\lang\Exception;

use org\jecat\framework\ui\xhtml\compiler\ExpressionCompiler;
use org\jecat\framework\ui\xhtml\Node;
use org\jecat\framework\lang\Type;
use org\jecat\framework\ui\ICompiler;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\xhtml\compiler\NodeCompiler;

class IncludeCompiler extends NodeCompiler 
{
	public function compile(IObject $aObject,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Type::check("org\\jecat\\framework\\ui\\xhtml\\Node",$aObject) ;
		$aAttributes = $aObject->attributes() ;

		if( $aAttributes->has("file") )
		{
			$sFileName = $aAttributes->get("file") ;		
		}
		else 
		{
			if( !$aFileVal = $aAttributes->anonymous() )
			{
				throw new Exception("include 节点缺少file属性(line:%d)",$aObject->line()) ;
			}
			$sFileName = '"' . addslashes($aFileVal->source()) . '"' ;
		}
		
		// 是否继承父模板中的变量
		$bExtendParentVars = $aAttributes->has("vars")? $aAttributes->bool('vars'): true ;
		
		// start
		$aDev->write("\r\n");
		
		// variables
		if(!$bExtendParentVars)
		{
			$aDev->write("\$__include_aVariables = new \\org\\jecat\\framework\\util\\DataSrc() ; \r\n");
			$aDev->write("\$__include_aVariables->addChild(\$aVariables) ;");
		}
		else
		{
			$aDev->write("\$__include_aVariables = \$aVariables ; \r\n");
		}
		
		// other variables
		foreach($aAttributes as $sName=>$aValue)
		{
			if( substr($sName,0,4)=='var.' and $sVarName=substr($sName,4) )
			{
				$sVarName = '"'. addslashes($sVarName) . '"' ;
				$sValue = ExpressionCompiler::compileExpression($aValue->source()) ;
				$aDev->write("\$__include_aVariables->set({$sVarName},{$sValue}) ; \r\n");
			}
		}
		
		$aDev->write("\$this->display({$sFileName},\$__include_aVariables,\$aDevice) ; ") ;		
	}
}
?>
