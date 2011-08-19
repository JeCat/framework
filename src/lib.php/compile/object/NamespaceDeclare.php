<?php
namespace jc\compile\object ;

use jc\compile\ClassCompileException;

class NamespaceDeclare extends Token
{
	public function __construct(Token $aToken)
	{
		if( $aToken->tokenType()!=T_NAMESPACE )
		{
			throw new ClassCompileException($aToken,"参数 \$aToken 必须是一个 T_NAMESPACE 类型的Token对象") ;
		}
		
		$this->cloneOf($aToken) ;
		$this->setBelongsNamespace($this) ;
	}
	
	public function addNameToken(Token $aToken)
	{
		if( $aToken->tokenType()!=T_STRING )
		{
			throw new ClassCompileException($aToken,"参数 \$aToken 必须是一个 T_STRING 类型的Token对象") ;
		}
		
		$this->arrNameAndSlashes[] = $aToken ;
	}
	
	public function name()
	{
		return implode("\\",$this->arrNameAndSlashes) ;
	}
	
	private $arrNameAndSlashes ;
}

?>