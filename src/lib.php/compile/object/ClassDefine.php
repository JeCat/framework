<?php
namespace jc\compile\object ;

use jc\compile\ClassCompileException;

class ClassDefine extends Token
{
	public function __construct(
			Token $aToken
			, $aTokenName=null
			, Token $aTokenBody=null
	)
	{
		$this->cloneOf($aToken) ;
		
		$this->aTokenName = $aTokenName ;
		$this->aTokenBody = $aTokenBody ;
		
		$this->setBelongsClass($this) ;
	}
	
	/**
	 * 返回正在定义的class的包括命名控件的完整名称
	 */
	public function fullName()
	{
		$aNamespace = $this->belongsNamespace() ;
		if($aNamespace)
		{
			return $aNamespace->name() . '\\' . $this->name() ;
		}
		else 
		{
			return $this->name() ;
		}
	}
	/**
	 * 返回正在定义的class的名称
	 */
	public function name()
	{
		return $this->aTokenName->sourceCode() ;
	}
	
	/**
	 * 返回定义class名称的token
	 */
	public function nameToken()
	{
		return $this->aTokenName ;
	}
	/**
	 * 设置定义class名称的token
	 */
	public function setNameToken(Token $aTokenName)
	{
		$this->aTokenName = $aTokenName ;
	}
	
	/**
	 * 返回class body 开始的大括号token
	 */
	public function bodyToken()
	{
		return $this->aTokenBody ;
	}
	/**
	 * 设置class body 开始的大括号token
	 */
	public function setBodyToken(ClosureToken $aTokenBody)
	{
		if( $aTokenBody->sourceCode()!='{' )
		{
			throw new ClassCompileException($aTokenBody,"参数 \$aTokenBody 必须是一个内容为 “{” 的Token对象") ;
		}
		
		$this->aTokenBody = $aTokenBody ;
	}
	
	private $aTokenName ;
	private $aTokenBody ;
}

?>