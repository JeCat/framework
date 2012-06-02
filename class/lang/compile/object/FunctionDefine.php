<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  这个文件是 JeCat PHP框架的一部分，该项目和此文件 均遵循 GNU 自由软件协议
// 
//  Copyleft 2008-2012 JeCat.cn(http://team.JeCat.cn)
//
//
//  JeCat PHP框架 的正式全名是：Jellicle Cat PHP Framework。
//  “Jellicle Cat”出自 Andrew Lloyd Webber的音乐剧《猫》（《Prologue:Jellicle Songs for Jellicle Cats》）。
//  JeCat 是一个开源项目，它像音乐剧中的猫一样自由，你可以毫无顾忌地使用JCAT PHP框架。JCAT 由中国团队开发维护。
//  正在使用的这个版本是：0.8
//
//
//
//  相关的链接：
//    [主页]			http://www.JeCat.cn
//    [源代码]		https://github.com/JeCat/framework
//    [下载(http)]	https://nodeload.github.com/JeCat/framework/zipball/master
//    [下载(git)]	git clone git://github.com/JeCat/framework.git jecat
//  不很相关：
//    [MP3]			http://www.google.com/search?q=jellicle+songs+for+jellicle+cats+Andrew+Lloyd+Webber
//    [VCD/DVD]		http://www.google.com/search?q=CAT+Andrew+Lloyd+Webber+video
//
////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*-- Project Introduce --*/
namespace org\jecat\framework\lang\compile\object ;

use org\jecat\framework\lang\compile\ClassCompileException;
use org\jecat\framework\pattern\iterate\ArrayIterator;

class FunctionDefine extends StructDefine
{
	public function __construct( Token $aToken, $aTokenName=null, Token $aTokenArgList=null, Token $aTokenBody=null )
	{
		parent::__construct($aToken,$aTokenName,$aTokenBody) ;
		
		if($aTokenArgList)
		{
			$this->setArgListToken($aTokenArgList) ;
		}
		
		$this->setBelongsFunction($this) ;
	}

	public function argListToken()
	{
		return $this->aTokenArgList ;
	}
	public function setArgListToken(ClosureToken $aTokenArgList)
	{
		if( $aTokenArgList->tokenType()!=Token::T_BRACE_ROUND_OPEN or $aTokenArgList->sourceCode()!='(' )
		{
			throw new ClassCompileException(null,$aTokenArgList,"参数 \$aTokenArgList 必须是一个内容为 “(” 的Token对象") ;
		}
		
		$this->aTokenArgList = $aTokenArgList ;
	}

	public function accessToken()
	{
		return $this->aAccessToken ;		
	}
	public function setAccessToken(Token $aAccessToken)
	{
		if( !in_array($aAccessToken->tokenType(),array(T_PRIVATE,T_PROTECTED,T_PUBLIC)) )
		{
			throw new ClassCompileException(null,$aAccessToken,"参数 \$aAccessToken 必须为 T_PRIVATE, T_PROTECTED 或 T_PUBLIC 类型的Token对象") ;
		} 
		
		$this->aAccessToken = $aAccessToken ;
	}
	public function staticToken()
	{
		return $this->aStaticToken ;		
	}
	public function setStaticToken(Token $aStaticToken)
	{
		if( $aStaticToken->tokenType()!==T_STATIC )
		{
			throw new ClassCompileException(null,$aStaticToken,"参数 \$aStaticToken 必须为 T_STATIC 类型的Token对象") ;
		} 
		
		$this->aStaticToken = $aStaticToken ;
	}
	public function abstractToken()
	{
		return $this->aAbstractToken ;		
	}
	public function setAbstractToken(Token $aAbstractToken)
	{
		if( $aAbstractToken->tokenType()!==T_ABSTRACT )
		{
			throw new ClassCompileException(null,$aAbstractToken,"参数 \$aAbstractToken 必须为 T_ABSTRACT 类型的Token对象") ;
		}
		$this->aAbstractToken = $aAbstractToken ;
	}

	public function startToken()
	{
		if( $aDocToken=$this->docToken() )
		{
			return $aDocToken ;
		}
		
		$arrTokens = array() ;
		
		foreach(array('aAccessToken','aStaticToken','aAbstractToken') as $sTokenName)
		{
			if($this->$sTokenName)
			{
				if( $aTokenPool = $this->$sTokenName->parent() )
				{
					$nPos = $aTokenPool->search($this->$sTokenName) ;
					if($nPos!==false)
					{
						$arrTokens[$nPos] = $this->$sTokenName ;
					}
				}
			}
		}
		
		if( empty($arrTokens) )
		{
			return $this ;
		}
		else
		{
			ksort($arrTokens) ;
			return array_shift($arrTokens) ;
		}
	}
	public function endToken()
	{
		if( $this->aEndToken )
		{
			return $this->aEndToken ;
		}
	
		if( $aBody = $this->bodyToken() )
		{
			return $aBody->theOther() ;
		}
		
		return null ;
	}
	public function setEndToken($aEndToken)
	{
		$this->aEndToken = $aEndToken ;
	}
	
	public function setReturnByRef($bReturnByRef){
		$this->bReturnByRef = $bReturnByRef ;
	}
	
	public function isReturnByRef(){
		return $this->bReturnByRef ;
	}
	
	public function addParameterToken($argumentToken){
		$this->arrParameterToken [] = $argumentToken;
	}
	
	public function parameterIterator(){
		return new ArrayIterator($this->arrParameterToken) ;
	}
	
	private $aTokenArgList ;
	private $aAccessToken ;
	private $aStaticToken ;
	private $aAbstractToken ;
	private $aEndToken ;
	private $arrParameterToken = array();
	private $bReturnByRef = false ;
}

