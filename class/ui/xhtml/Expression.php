<?php
namespace org\jecat\framework\ui\xhtml ;

use org\jecat\framework\ui\TargetCodeOutputStream;

class Expression
{
	public function __construct($sSource,$bForceEval=false,$bAloneLine=false)
	{
		$this->sSource = $sSource ;
		$this->bForceEval = $bForceEval ;
		$this->bAloneLine = $bAloneLine ;
	}
	
	public function __toString()
	{
		trigger_error('不应该将一个 Expression 对像简单地用作字符串。',E_USER_DEPRECATED ) ;
		return $this->generate() ;
	}
	
	public function generate(TargetCodeOutputStream $aDev=null,$sSubTemplate=null)
	{
		$sSource = trim($this->sSource) ;
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
					
					if($aDev)
					{
						$aDev->declareVarible($sVarName,"& \$aVariables->getRef('{$sVarName}')",$sSubTemplate) ;
					}
					else
					{
						$sVarName = 'aVariables->'.$sVarName ;
					}
						
					$sLineCode.= '$'.$sVarName ;
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
		
		
		if( $this->bForceEval )
		{
			$sCompiled = implode(";", $arrLines) ;
		
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
			$sCompiled = "eval(\"" . $sCompiled . "\")" ;
		}
		else
		{
			$sCompiled = implode(";\r\n", $arrLines) ;
		
			if($this->bAloneLine)
			{
				if( !preg_match("/;\\s*$/",$sCompiled) )
				{
					$sCompiled.= ';' ;
				}
			}
		}
		
		if($aDev)
		{
			$aDev->putCode($sCompiled,$sSubTemplate) ;
		}
		else
		{
			return $sCompiled ;
		}
	}
	
	
	private $sSource ;
	private $bForceEval ;
	private $bAloneLine ;
}

