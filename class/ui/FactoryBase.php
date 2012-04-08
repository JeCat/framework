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
namespace org\jecat\framework\ui ;

use org\jecat\framework\lang\Object as JcObject;

abstract class FactoryBase extends JcObject implements IFactory 
{
	/**
	 * return UI
	 */
	public function create()
	{
		$aUI = new UI($this) ;
		$aUI->setApplication($this->application(true)) ;

		return $aUI ;
	}
	
	/**
	 * return SourceFileManager
	 */
	public function createSourceFileManager()
	{
		return $this->sourceFileManager() ;
	}
	/**
	 * return SourceFileManager
	 */
	public function sourceFileManager()
	{
		if(!$this->aSourceFileManager)
		{
			$this->aSourceFileManager = $this->newSourceFileManager() ;
		}
		
		return $this->aSourceFileManager ;
	}
	/**
	 * @return SourceFileManager
	 */
	public function newSourceFileManager()
	{
		$aSourceFileManager = SourceFileManager::singleton(true) ;
		$aSourceFileManager->setApplication($this->application(true)) ;
		
		return $aSourceFileManager ;
	}
	public function setSourceFileManager(SourceFileManager $aSrcMgr)
	{
		$this->aSourceFileManager = $aSrcMgr ;
	}

	/**
	 * return CompilerManager
	 */
	public function createCompilerManager()
	{
		return $this->compilerManager() ;
	}
	/**
	 * return CompilerManager
	 */
	public function compilerManager()
	{
		if( !$this->aCompilers )
		{
			$this->aCompilers = $this->newCompilerManager() ;
		}
		return $this->aCompilers ;
	}
	/**
	 * @return CompilerManager
	 */
	public function newCompilerManager()
	{
		$aCompilers = CompilerManager::singleton(true) ;
		$aCompilers->setApplication($this->application(true)) ;
		
		return $aCompilers ;
	}
	public function setCompilerManager(CompilerManager $aCompilers)
	{
		$this->aCompilers = $aCompilers ;
	}
	
	/**
	 * return InterpreterManager
	 */
	public function createInterpreterManager()
	{
		return $this->interpreterManager() ;
	}
	/**
	 * return InterpreterManager
	 */
	public function interpreterManager()
	{
		if( !$this->aInterpreters )
		{
			$this->aInterpreters = $this->newInterpreterManager() ;
		}
		return $this->aInterpreters ;
	}
	/**
	 * @return InterpreterManager
	 */
	public function newInterpreterManager()
	{
		$aInterpreters = InterpreterManager::singleton(true) ;
		$aInterpreters->setApplication($this->application(true)) ;

		return $aInterpreters ;
	}
	public function setInterpreter(InterpreterManager $aInterpreters)
	{
		$this->aInterpreters = $aInterpreters ;
	}
	
	protected $aSourceFileManager ;
	protected $aCompilers ;
	protected $aInterpreters ;
}
