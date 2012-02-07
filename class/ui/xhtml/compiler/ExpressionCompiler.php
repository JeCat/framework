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
		$aDev->write(self::compileExpression($aObject->source(),$aObjectContainer->variableDeclares())) ;
	}

	static public function compileExpression($sSource,VariableDeclares $aVarDeclares,$bForceEval=false,$bAloneLine=false)
	{
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
					$sVarNameNew = 'aVariables->'.$sVarName ;
					
					// 声明变量
					$aVarDeclares->declareVarible($sVarName,$sVarNameNew) ;
					
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
		
		$sCompiled = implode(";", $arrLines) ;
		
		if( count($arrLines)>1 or $bForceEval )
		{
			// return 最后一行
			$arrLines[] = 'return ' . array_pop($arrLines) ;
			
			// 为 eval 转义
			$sCompiled = addcslashes($sCompiled,'"\\') ;	
			$sCompiled = str_replace('$','\\$',$sCompiled) ;
			
			// 末尾加上 ;
			if( !preg_match("/;\\s*$/",$sCompiled) )
			{
				$sCompiled.= ';' ;
			}
			
			// 套上 eval 返回
			return "eval(\"" . $sCompiled . "\")" ;
		}
		else
		{
			if($bAloneLine)
			{
				if( !preg_match("/;\\s*$/",$sCompiled) )
				{
					$sCompiled.= ';' ;
				}
			}
			
			return $sCompiled ;
		}
	}
}

?>