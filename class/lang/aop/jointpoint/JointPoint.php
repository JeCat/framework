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
namespace org\jecat\framework\lang\aop\jointpoint ;

use org\jecat\framework\lang\aop\compiler\ClassInfoLibrary;
use org\jecat\framework\lang\aop\Pointcut;
use org\jecat\framework\lang\Object;
use org\jecat\framework\lang\compile\object\Token;
use org\jecat\framework\lang\Exception;

abstract class JointPoint extends Object implements \Serializable
{
	const ACCESS_SET = 'set' ;
	const ACCESS_GET = 'get' ;
	const ACCESS_ANY = '*' ;

	/**
	 * @return JointPoint
	 */
	static public function createDefineMethod($sClassName,$sMethodNamePattern='*',$bMatchDerivedClass=false)
	{
		return new JointPointMethodDefine($sClassName,$sMethodNamePattern,$bMatchDerivedClass=false) ;
	}
	
	/**
	 * @return JointPoint
	 */
	static public function createCallFunction($sCallFunctionNamePattern,$sWeaveClass,$sWeaveMethodNamePattern='*')
	{
		return new JointPointCallFunction($sCallFunctionNamePattern,$sWeaveClass,$sWeaveMethodNamePattern) ;
	}
	
	/**
	 * @return JointPoint
	 */
	static public function createAccessProperty($sCallPropertyNamePattern,$sWeaveClass,$sWeaveMethodNamePattern='*',$sAccess=self::ACCESS_ANY)
	{
		if( !in_array($sAccess, array(self::ACCESS_SET,self::ACCESS_GET,self::ACCESS_ANY)) )
		{
			throw new Exception('参数$sAccess值不合法，必须为：%s，输入值为“%s”',array(implode(',', array(self::ACCESS_SET,self::ACCESS_GET,self::ACCESS_ANY)),$sAccess)) ;
		}
		
		$aJointPoint = new self() ;
		$aJointPoint->setExecutionPattern("->\${$sCallPropertyNamePattern} {$sAccess}") ;
		$aJointPoint->setWeaveClass($sWeaveClass) ;
		$aJointPoint->setWeaveMethod($sWeaveMethodNamePattern) ;
		return $aJointPoint ;
	}
	
	/**
	 * @return JointPoint
	 */
	static public function createThrowException($sThrowClassNamePattern,$sWeaveClass,$sWeaveMethodNamePattern='*')
	{
		$aJointPoint = new self() ;
		$aJointPoint->setExecutionPattern("throw {$sThrowClassNamePattern}") ;
		$aJointPoint->setWeaveClass($sWeaveClass) ;
		$aJointPoint->setWeaveMethod($sWeaveMethodNamePattern) ;
		return $aJointPoint ;
	}
	
	/**
	 * @return JointPoint
	 */
	static public function createNewObject($sNewClassNamePattern,$sWeaveClass,$sWeaveMethodNamePattern='*')
	{
		return new JointPointNewObject($sNewClassNamePattern,$sWeaveClass,$sWeaveMethodNamePattern) ;
	}
	
	
	//////////////////////////////////////////////////////////////////
	
	public function __construct($sWeaveClass=null,$sWeaveMethod='*',$bMatchDerivedClass=false)
	{
		$this->setWeaveClass($sWeaveClass) ;
		$this->setWeaveMethod($sWeaveMethod) ;
		$this->bMatchDerivedClass = $bMatchDerivedClass ;
	}
	
	abstract static public function createFromDeclare($sDeclare) ;
	
	abstract public function exportDeclare($bWithClass=true) ;
		
	static public function transRegexp($sPartten)
	{
		$sPartten = preg_quote($sPartten) ;
		$sPartten = str_replace('\\*', '.*', $sPartten) ;
		
		return '`' . $sPartten . '`is' ;
	}
	
	public function setWeaveClass($sWeaveClass)
	{
		$this->sWeaveClass = $sWeaveClass ;
	}
	public function weaveClass()
	{
		return $this->sWeaveClass ;
	}
	public function setWeaveMethod($sWeaveMethod)
	{
		$this->sWeaveMethod = $sWeaveMethod ;
		$this->sWeaveMethodNameRegexp = self::transRegexp($sWeaveMethod) ;
	}
	public function weaveMethod()
	{
		return $this->sWeaveMethod ;
	}
	public function weaveMethodNameRegexp()
	{
		return $this->sWeaveMethodNameRegexp ;
	}
	public function matchWeaveMethod(Token $aToken)
	{
		if( !$aClass=$aToken->belongsClass() or $aMethod=$aToken->belongsFunction() )
		{
			return false ;
		}
		
		if( $aClass->fullName()!=$this->weaveClass() )
		{
			return false ;
		}
		
		return preg_match( $this->weaveMethodNameRegexp(), $aMethod->name() ) ;
	}
	
	public function weaveMethodIsPattern()
	{
		return preg_match('/^[^\w_]+$/',$this->sWeaveMethod) ;
	}
	
	abstract public function matchExecutionPoint(Token $aToken) ;
	
	public function matchClass($sTargetClass)
	{
		if( $this->bMatchDerivedClass )
		{
			return ClassInfoLibrary::singleton()->isA($sTargetClass,$this->weaveClass()) ;
		}
		else
		{
			return $this->weaveClass() == $sTargetClass ; 
		}
	}
	
	public function setPointcut(Pointcut $aPointcut)
	{
		$this->aPointcut = $aPointcut ;
	}
	public function pointcut()
	{
		return $this->aPointcut ;
	}
	
	public function serialize ()
	{
		return serialize( array(
				'sWeaveClass' => & $this->sWeaveClass ,
				'sWeaveMethod' => & $this->sWeaveMethod ,
				'sWeaveMethodNameRegexp' => & $this->sWeaveMethodNameRegexp ,
				'bMatchDerivedClass' => & $this->bMatchDerivedClass ,
		) ) ;
	}
	
	/**
	 * @param serialized
	 */
	public function unserialize ($serialized)
	{
		$arrData = unserialize($serialized) ;
	
		$this->sWeaveClass =& $arrData['sWeaveClass'] ;
		$this->sWeaveMethod =& $arrData['sWeaveMethod'] ;
		$this->sWeaveMethodNameRegexp =& $arrData['sWeaveMethodNameRegexp'] ;
		$this->bMatchDerivedClass =& $arrData['bMatchDerivedClass'] ;
	}
	
	public function isMatchDerivedClass()
	{
		return $this->bMatchDerivedClass ;
	}
	
	private $sWeaveClass ;

	private $bMatchDerivedClass = false ;
	
	private $sWeaveMethod ;
	
	private $sWeaveMethodNameRegexp ;
	
	private $aPointcut ;
	
	protected $arrBeanConfig ;
}

