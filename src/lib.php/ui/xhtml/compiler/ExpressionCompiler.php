<?php

namespace jc\ui\xhtml\compiler ;

use jc\io\IOutputStream;
use jc\ui\CompilerManager;
use jc\ui\IObject;

class ExpressionCompiler extends BaseCompiler
{
	public function compile(IObject $aObject,IOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		$aDev->write(self::compileExpression($aObject->source())) ;
	}

	static public function compileExpression($sSource,$bEval=true,$bReturn=true)
	{
		if( !$bEval )
		{
			$bReturn = false ;
		}
		
		$nStackId = self::$nStackVarId++ ;
				
		// 补行尾的';'
		$sSource = trim($sSource) ;
		if( substr($sSource,-1)!=';' )
		{
			$sSource.= ';' ;
		}
		
		// 分解
		$arrTokens = token_get_all('<?php '.$sSource.'?>') ;
		array_shift($arrTokens) ;
		array_pop($arrTokens) ;
		
		$sLineCode = '' ;
		$arrVarDefineLines = array() ;
		$arrVarResaveLines = array() ;
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
					$sVarNameNew = NodeCompiler::assignVariableName('_stack').'_var_'.$sVarName ;
					
					//
					if($bEval)
					{
						$arrVarDefineLines[$sVarName] = '\\$'.$sVarNameNew."=\\\$aVariables->get('{$sVarName}')" ;
						$arrVarResaveLines[$sVarName] = "\\\$aVariables->set('{$sVarName}',\\\${$sVarNameNew})" ;
						$sLineCode.= '\\$'.$sVarNameNew ;
					}
					else 
					{
						$arrVarDefineLines[$sVarName] = '$'.$sVarNameNew."=\$aVariables->get('{$sVarName}')" ;
						$arrVarResaveLines[$sVarName] = "\$aVariables->set('{$sVarName}',\${$sVarNameNew})" ;
						$sLineCode.= '$'.$sVarNameNew ;
					}
				}
				else 
				{
					// 转义作为字符的$ （将 \$ 替换为 \\\$）
					if($bEval)
					{
						$sLine = str_replace("\\\$","\\\\\\\$",$arrOneTkn[1]) ;
					}
					else 
					{
						$sLine = $arrOneTkn[1] ;
					}
					
					$sLineCode.= $sLine ;
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
		
		// return 最末行的结果
		if( $bReturn )
		{
			$sLastLine = array_pop($arrLines) ;
		}
		
		// 合并 变量声明行, 执行行 和 变量保存行
		$arrLines = array_merge(array_values($arrVarDefineLines),$arrLines,$arrVarResaveLines) ;
		
		if(!empty($sLastLine))
		{
			$arrLines[] = 'return ' . $sLastLine ;
		}
		
		// 
		$sCompiled = implode(";\r\n", $arrLines) ;
		if( $bEval )
		{
			$sCompiled = addcslashes($sCompiled,'"') ;		
		}
		
		if( !preg_match("/;\s*$/",$sCompiled) )
		{
			$sCompiled.= ';' ;
		}
		
		return $bEval? ("eval(\"" . $sCompiled . "\")"): $sCompiled ;
	}
	
	static private $nStackVarId = 0 ;
}

?>