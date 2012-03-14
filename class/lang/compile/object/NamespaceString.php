<?php
namespace org\jecat\framework\lang\compile\object ;

use org\jecat\framework\lang\compile\interpreters\oop\State;
use org\jecat\framework\lang\compile\ClassCompileException;

class NamespaceString extends Token
{
	public function addNameToken(Token $aToken)
	{
		if( $aToken->tokenType()==T_STRING )
		{
			$this->arrNameAndSlashes[] = $aToken ;
		}
		else if( $aToken->tokenType()==T_NS_SEPARATOR )
		{
			// 接受表示绝对路径的开头的 斜线
			if(empty($this->arrNameAndSlashes))
			{
				$this->arrNameAndSlashes[] = $aToken ;
			}
		}
		else
		{
			throw new ClassCompileException(null,$aToken,"参数 \$aToken 必须是一个 T_STRING 类型的Token对象") ;
		}
		
	}
	
	public function name()
	{
		return implode("\\",$this->arrNameAndSlashes) ;
	}
	
	public function findRealName(TokenPool $aTokenPool)
	{
		if(empty($this->arrNameAndSlashes))
		{
			return null ;
		}
		
		$arrNameAndSlashes = $this->arrNameAndSlashes ;
		$aFirtToken = array_shift($arrNameAndSlashes) ;
		
		// 绝对路径
		if( $aFirtToken->tokenType()==T_NS_SEPARATOR )
		{
			return '\\'.implode("\\",$arrNameAndSlashes) ;
		}
		else
		{
			$sName = $aTokenPool->findName($aFirtToken->targetCode(),$this->belongsNamespace()) ;
			if(!empty($arrNameAndSlashes))
			{
				$sName.= '\\' . implode("\\",$arrNameAndSlashes) ;
			}
			return $sName ;
		}
	}
	
	public function endToken(){
		return end($this->arrNameAndSlashes);
	}
	
	protected $arrNameAndSlashes ;
}

?>