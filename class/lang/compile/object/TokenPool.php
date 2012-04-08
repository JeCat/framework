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
//  正在使用的这个版本是：0.7.1
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

use org\jecat\framework\pattern\iterate\ArrayIterator;
use org\jecat\framework\lang\compile\ClassCompileException;
use org\jecat\framework\pattern\composite\Container;

class TokenPool extends Container
{
	public function __construct($sSourceFilepath=null)
	{
		parent::__construct('org\\jecat\\framework\\lang\\compile\\object\\AbstractObject') ;
		$this->sSourceFilepath = $sSourceFilepath ;
	}
	
	public function add($object,$sName=null,$bAdoptRelative=true)
	{
		parent::add($object,$sName,$bAdoptRelative) ;
	}
	
	public function addClass(ClassDefine $aClass)
	{
		$this->arrClasses[$aClass->fullName()] = $aClass ;
	}
	
	public function addFunction(FunctionDefine $aFunction)
	{
		if( $aClass=$aFunction->belongsClass() )
		{
			$sClassName = $aClass->fullName() ;
		}
		else
		{
			$sClassName = '' ;
		}
		
		if( !$sFuncName = $aFunction->name() )
		{
			$sFuncName = '' ;
		}
		
		$this->arrMethods[$sClassName][$sFuncName] = $aFunction ;
	}

	public function findClass($sClassName)
	{
		return isset($this->arrClasses[$sClassName])? $this->arrClasses[$sClassName]: null ;
	}
	
	public function findFunction($sFunctionName,$sClassName='')
	{
		return isset($this->arrMethods[$sClassName][$sFunctionName])? $this->arrMethods[$sClassName][$sFunctionName]: null ;
	}
	
	/**
	 * @return Token
	 */
	public function findTokenBySource($sSource,$nSeek=0)
	{
		$nFound = 0 ;
		foreach($this->iterator() as $aToken)
		{
			if( $aToken->sourceCode()===$sSource )
			{
				if($nSeek===$nFound++)
				{
					return $aToken ;
				}
			}
		}
		
		return ;
	}

	public function classIterator()
	{
		return new ArrayIterator($this->arrClasses) ;
	}
	public function functionIterator($sClassName='')
	{
		return isset($this->arrMethods[$sClassName])?
					new ArrayIterator($this->arrMethods[$sClassName]):
					new ArrayIterator() ;
	}
	
	
	public function addUseDeclare(UseDeclare $aUseToken)
	{
		if( !$sName = $aUseToken->name() )
		{
			throw new ClassCompileException(null,$aUseToken,"编译class时遇到无效的 use 关键词") ;
		}
	
		$this->arrNamespaces[$sName] = $aUseToken->fullName() ;
	}
	
	public function findName($name,NamespaceDeclare $aBelongNamespace=null)
	{
		if( $name instanceof Token )
		{
			$sName = $name->sourceCode() ;
			if( $name->belongsNamespace() )
			{
				$aBelongNamespace = $name->belongsNamespace() ;
			}
		}
		else
		{
			$sName = (string)$name ;
		}
		
		if( isset($this->arrNamespaces[$sName]) )
		{
			return $this->arrNamespaces[$sName] ;
		}
		else if( $aBelongNamespace )
		{
			return $aBelongNamespace->name() . '\\' . $sName ;
		}
		else
		{
			return $sName ;
		}
	}
	
	public function sourcePath()
	{
		return $this->sSourceFilepath ;
	}
	
	private $arrClasses = array() ;
	private $arrMethods = array() ;
	private $arrNamespaces ;
	private $sSourceFilepath ;
}

