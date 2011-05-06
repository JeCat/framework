<?php

namespace jc\ui\xhtml ;

use jc\util\match\RegExp;
use jc\lang\Object ;

class ExpressionCompiler extends Object
{
	static public function compile($sSource)
	{
		$aRegexpFoundExpression = new RegExp("/\\{([\\*=\\?])(.+)\\}/s") ;
		
		$aResSet = $aRegexpFoundExpression->match($sSource) ;
		$aResSet->reverse() ;
		foreach( $aResSet as $aRes )
		{
			$sType = $aRes->result(1) ;
			
			// 注释
			if($sType=='*')
			{
				return '' ;
			}
			
			$sExpression = $aRes->result(2) ;
			
			switch ($sType)
			{
			// 执行	
			case '?' :
				$sCompiled = "<?php " . self::compileExpression($sExpression,false) . " ;?>" ;
				 
			// 输出
			case '=' :
				$sCompiled = "<?php echo " . self::compileExpression($sExpression,true) . " ;?>" ;
			}
			
			$sSource = substr_replace($sSource,$sCompiled,$aRes->position(),$aRes->length()) ;
		}
		
		return $sSource ;
	}

	static public function compileExpression($sSource)
	{
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
					$sVarNameNew = '_stack'.$nStackId.'_var_'.$sVarName ;
					
					//
					$arrVarDefineLines[$sVarName] = '\\$'.$sVarNameNew."=\\\$aVariables->get('{$sVarName}')" ;
					$sLineCode.= '\\$'.$sVarNameNew ;
				}
				else 
				{
					// 转义作为字符的$ （将 \$ 替换为 \\\$）
					$sLine = str_replace("\\\$","\\\\\\\$",$arrOneTkn[1]) ;
					
					$sLineCode.= $sLine ;
				}
			}
			// 行尾
			else if($arrOneTkn==';')
			{
				$sLineCode = trim($sLineCode) ;
				if($sLineCode)
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
		$sLastLine = array_pop($arrLines) ;
		$arrLines[] = 'return ' . $sLastLine ;
		
		// 合并 变量声明行 和 执行行
		$arrLines = array_merge(array_values($arrVarDefineLines),$arrLines) ;
		
		// 
		$sCompiled = implode(";\r\n", $arrLines).";" ;		
		$sCompiled = addcslashes($sCompiled,'"') ;
		
		return "eval(\"" . $sCompiled . "\")" ;
	}
	
	private $aRegexpFoundExpression ;
	private $aRegexpFoundVariable ;
	
	static private $nStackVarId = 0 ;
}

?>