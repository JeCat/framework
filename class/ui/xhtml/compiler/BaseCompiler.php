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
namespace org\jecat\framework\ui\xhtml\compiler ;

use org\jecat\framework\ui\ICompiler;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\ObjectContainer;
use org\jecat\framework\lang\Object as JcObject;

class BaseCompiler extends JcObject implements ICompiler
{
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{	
		if( $aObject instanceof \org\jecat\framework\ui\xhtml\ObjectBase and !$aObject->count() )
		{
			$aDev->write($aObject->source()) ;
		}
		
		else 
		{
			$this->compileChildren($aObject,$aObjectContainer,$aDev,$aCompilerManager) ;
		}
	}
	
	public function compileStrategySignture()
	{
		return md5(__CLASS__. var_export($this->arrCompilers,true)) ;
	}
		
	protected function compileChildren(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		foreach ($aObject->iterator() as $aChild)
		{
			if( $aCompiler = $aCompilerManager->compiler($aChild) )
			{
				$aCompiler->compile($aChild,$aObjectContainer,$aDev,$aCompilerManager) ;
			}
		}
	}
	
	
	// sub compiler ---------------------------------------------------------------
	public function addSubCompiler($sName,$sCompilerClass) 
	{
		$sName = strtolower($sName) ;
		if( !isset($this->arrCompilers[ $sName ]) )
		{
			$this->arrCompilers[ $sName ] = $sCompilerClass ;
		}
	}
	public function setSubCompiler($sName,$sCompilerClass) 
	{
		$this->arrCompilers[ strtolower($sName) ] = $sCompilerClass ;
	}
	public function removeSubCompiler($sName)
	{
		unset($this->arrCompilers[ strtolower($sName) ]) ;
	}
	public function clearSubCompiler()
	{
		$this->arrCompilers = array() ;
	}

	/**
	 * @return ICompiler
	 */
	public function subCompiler($sName)
	{
		if( !isset($this->arrCompilers[$sName]) )
		{
			if( !isset($this->arrCompilers['*']) )
			{
				return null ;				
			}
			else 
			{
				$sName = '*' ;
			}
		}
		
		if( is_string($this->arrCompilers[$sName]) )
		{
			$this->arrCompilers[$sName] = new $this->arrCompilers[$sName]() ;
		}
		
		return $this->arrCompilers[$sName] ;
	}
	
	private $arrCompilers = array() ;
}

