<?php
namespace org\jecat\framework\lang\compile\object ;

use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\compile\ClassCompileException;

abstract class StructDefine extends Token
{
	public function __construct(Token $aToken, $aTokenName=null, Token $aTokenBody=null)
	{
		$this->cloneOf($aToken) ;
		
		if($aTokenName)
		{
			$this->setNameToken($aTokenName) ;
		}
		if($aTokenBody)
		{
			$this->setBodyToken($aTokenBody) ;
		}
	}

	/**
	 * 返回正在定义的class的名称
	 */
	public function name()
	{
		return $this->aTokenName? $this->aTokenName->sourceCode(): null ;
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
		if( $aTokenName->tokenType()!==T_STRING )
		{
			throw new ClassCompileException(null,$aTokenName,"参数 \$aTokenName 必须是一个 T_STRING 类型 token 对象") ;
		}
		
		$this->aTokenName = $aTokenName ;
	}
	
	/**
	 * 返回class body 开始的大括号token
	 * @return ClosureToken
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
		if( $aTokenBody->tokenType()!=Token::T_BRACE_OPEN or $aTokenBody->sourceCode()!='{' )
		{
			throw new ClassCompileException(null,$aTokenBody,"参数 \$aTokenBody 必须是一个内容为 “{” 的Token对象") ;
		}
		
		$this->aTokenBody = $aTokenBody ;
	}
	
	/**
	 * @return DocCommentDefine
	 */
	public function docToken()
	{
		return $this->aDocToken ;		
	}
	public function setDocToken(DocCommentDeclare $aDocToken)
	{
		$this->aDocToken = $aDocToken ;
	}

	public function bodySource()
	{
		if( !$this->aTokenBody )
		{
			return null ;
		}

		if( !$aTokenPool = $this->parent() )
		{
			throw new ClassCompileException(null,$this,"%s 对象不属于一个 TokenPool 对象",get_class($this)) ;
		}
		
		$aIter = $aTokenPool->iterator() ;
		$nPos = $aIter->search($this->aTokenBody) ;
		
		if( $nPos===false )
		{
			throw new ClassCompileException(null,"%s 对象的 bodyToken 无效",get_class($this),$this) ;
		}
		
		$aIter->seek($nPos) ;
		$aIter->next() ;
		
		$sSource = '' ;
		while( $aToken=$aIter->current() and $aToken!==$this->aTokenBody->theOther() )
		{
			$sSource.= $aToken->sourceCode() ;
			$aIter->next() ;
		}
		
		return $sSource ;
	}
	
	private $aTokenName ;
	private $aTokenBody ;
	private $aDocToken ;
}

?>