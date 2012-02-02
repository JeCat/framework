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
use org\jecat\framework\ui\ObjectContainer;

/**
 * @wiki /模板引擎/标签
 *
 * {|
 * 	!<include>
 *  !可单行
 *  !在所在位置载入另一个模板文件
 *  |--- ---
 *  !属性
 *  !
 *  !类型
 *  !默认值
 *  !说明
 *  |--- ---
 *  |@匿名/file
 *  |必须
 *  |string
 *  |
 *  |模板文件名，在模板目录中的相对路径。如果有多个不同namespace的模板目录，可以使用 namespace:templateFilename 的格式
 *  |--- ---
 *  |vars
 *  |可选
 *  |bool
 *  |true
 *  |条件表达式
 *  |}
 */

class IncludeCompiler extends NodeCompiler 
{
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
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
				$sValue = ExpressionCompiler::compileExpression($aValue->source(),$aObjectContainer->variableDeclares()) ;
				$aDev->write("\$__include_aVariables->set({$sVarName},{$sValue}) ; \r\n");
			}
		}
		
		$aDev->write("\$this->display({$sFileName},\$__include_aVariables,\$aDevice) ; ") ;		
	}
}
?>
