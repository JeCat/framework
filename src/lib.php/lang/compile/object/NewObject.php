<?php
namespace jc\lang\compile\object ;

use jc\lang\compile\ClassCompileException;

class NewObject extends Token
{
	
	public function __construct(Token $aToken,Token $aTokenName,Token $aArgv=null)
	{
		//自身必须是"new" token
		if( $aToken->tokenType() !== T_NEW )
		{
			throw new ClassCompileException(null,$aToken,"参数 \$aToken 必须是一个 T_NEW 类型的Token对象") ;
		}
		
		//name token 必须是 命名空间,变量,字符串中的一种
		if( $aTokenName->tokenType() !== T_NAMESPACE 
			&& $aTokenName->tokenType() !== T_VARIABLE 
			&& $aTokenName->tokenType() !== T_STRING )
		{
			throw new ClassCompileException(null,$aTokenName,"参数 \$aTokenName 必须是一个合法的类名Token对象") ;
		}
		
		$this->cloneOf($aToken) ;
		if($aArgv !== null)
		{
			$this->setArgvToken($aArgv) ;
		}
	}
	
	public function setArgvToken(Token $aArgv)
	{
		$this->aArgv = $aArgv ;
	}
	public function argvToken()
	{
		return $this->aArgv;
	}
	
	private $aArgv ;
}

?>