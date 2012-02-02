<?php

namespace org\jecat\framework\ui\xhtml\compiler ;

use org\jecat\framework\ui\VariableDeclares;

use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\ObjectContainer;

class ExpressionCompiler extends BaseCompiler
{
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		debug_print_backtrace() ;
		$aDev->write(self::compileExpression($aObject->source(),$aObjectContainer->variableDeclares())) ;
	}

	static public function compileExpression($sSource,VariableDeclares $aVarDeclares,$bEval=true,$bReturn=true)
	{
		if( !$bEval )
		{
			$bReturn = false ;
		}
		
		$sSource = trim($sSource) ;
		if( !preg_match("/;\\s*/", $sSource) )
		{
			$sSource.= ';' ;
		}
		
		// 分解
		$arrTokens = token_get_all('<?php '.$sSource.'?>') ;
		array_shift($arrTokens) ;
		array_pop($arrTokens) ;
		
		$sLineCode = '' ;
		$arrVarDefineLines = array() ;
		$arrLines = array() ;
		foreach($arrTokens as $arrOneTkn)
		{
			if( is_array($arrOneTkn) )
			{
				// 变量
				if($arrOneTkn[0]==T_VARIABLE)
				{
					// 变量名
					$sVarName = substr($arrOneTkn[1],1) ;
					$sVarNameNew = '__uivar_'.$sVarName ;
					
					// 声明变量
					$aVarDeclares->declareVarible($sVarName,$sVarNameNew) ;
					
					// $arrVarDefineLines[$sVarName] = "if(!isset(\${$sVarNameNew})){ \${$sVarNameNew}=&\$aVariables->getRef('{$sVarName}') ;}" ;
					$sLineCode.= '$'.$sVarNameNew ;
				}
				else 
				{
					$sLineCode.= $arrOneTkn[1] ;
				}
			}
			// 行尾
			else if($arrOneTkn==';')
			{
				$sLineCode = trim($sLineCode) ;
				if( $sLineCode!=='' or $sLineCode!==null )
				{
					$arrLines[] = $sLineCode ;
					$sLineCode = '' ;
				}
			}
			else
			{
				$sLineCode.= $arrOneTkn ;
			}
		}
		
		// 合并 变量声明行, 执行行 和 变量保
		$arrLines = array_merge(array_values($arrVarDefineLines),$arrLines) ;
		
		// return 最末行的结果
		if( $bReturn )
		{
			$arrLines[] = 'return ' . array_pop($arrLines) ;
		}
		
		// 
		$sCompiled = implode(";", $arrLines) ;
		if( $bEval )
		{
			$sCompiled = addcslashes($sCompiled,'"\\') ;	
			$sCompiled = str_replace('$','\\$',$sCompiled) ;		
		}
		
		if( !preg_match("/;\\s*$/",$sCompiled) )
		{
			$sCompiled.= ';' ;
		}
		
		return $bEval? ("eval(\"" . $sCompiled . "\")"): $sCompiled ;
	}
}

?>